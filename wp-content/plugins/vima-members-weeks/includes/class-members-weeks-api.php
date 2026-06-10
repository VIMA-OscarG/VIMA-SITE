<?php

if (!defined('ABSPATH')) exit;

class Members_Weeks_API
{
    public function register_routes()
    {
        register_rest_route('members-weeks/v1', '/submissions', [
            [
                'methods'  => 'GET',
                'callback' => [$this, 'list_submissions'],
                'permission_callback' => ['Members_Weeks_Auth', 'check_request'],
            ],
            [
                'methods'  => 'POST',
                'callback' => [$this, 'create_submission'],
                'permission_callback' => ['Members_Weeks_Auth', 'check_request'],
            ],
        ]);

        register_rest_route('members-weeks/v1', '/submissions/(?P<id>\d+)', [
            [
                'methods'  => 'POST',
                'callback' => [$this, 'update_submission'],
                'permission_callback' => ['Members_Weeks_Auth', 'check_request'],
            ],
            [
                'methods'  => 'DELETE',
                'callback' => [$this, 'delete_submission'],
                'permission_callback' => ['Members_Weeks_Auth', 'check_request'],
            ],
        ]);

        register_rest_route('members-weeks/v1', '/agreement', [
            [
                'methods'  => 'GET',
                'callback' => [$this, 'get_agreement'],
                'permission_callback' => ['Members_Weeks_Auth', 'check_request'],
            ],

        
        ]);
        register_rest_route('members-weeks/v1', '/preferred-rentals', [
            [
                'methods'  => 'GET',
                'callback' => [$this, 'get_preferred_rental_request'],
                'permission_callback' => ['Members_Weeks_Auth', 'check_request'],
            ],
            [
                'methods'  => 'POST',
                'callback' => [$this, 'save_preferred_rental_request'],
                'permission_callback' => ['Members_Weeks_Auth', 'check_request'],
            ],
        ]);

    }

    public function list_submissions(\WP_REST_Request $request)
    {
        $email = sanitize_email((string) $request->get_param('email'));
        $wp_user_id = absint($request->get_param('wp_user_id'));

        $metaQuery = ['relation' => 'OR'];

        if ($email) {
            $metaQuery[] = [
                'key'   => 'email',
                'value' => $email,
            ];
        }

        if ($wp_user_id) {
            $metaQuery[] = [
                'key'   => 'wp_user_id',
                'value' => $wp_user_id,
            ];
        }

        if (count($metaQuery) === 1) {
            return rest_ensure_response([
                'success' => true,
                'items'   => [],
            ]);
        }

        $posts = get_posts([
            'post_type'      => 'rental-submissions',
            'post_status'    => ['publish', 'draft', 'pending', 'private'],
            'posts_per_page' => 200,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'meta_query'     => $metaQuery,
        ]);

        $items = array_map(function ($post) {
            return $this->format_submission($post->ID);
        }, $posts);

        return rest_ensure_response([
            'success' => true,
            'items'   => $items,
        ]);
    }

    public function create_submission(\WP_REST_Request $request)
    {
        $data = $this->validate_submission_payload($request);

        if (is_wp_error($data)) {
            return $data;
        }

        $post_id = wp_insert_post([
            'post_type'   => 'rental-submissions',
            'post_status' => 'publish',
            'post_title'  => $data['unit_name'] . ' | ' . $data['week_start'] . ' - ' . $data['week_end'],
            'post_author' => !empty($data['wp_user_id']) ? (int) $data['wp_user_id'] : 0,
        ], true);

        wp_update_post([
            'ID' => $post_id,
            'post_title' => 'Rental Submission #' . $post_id,
            'post_name' => 'rental-submission-' . $post_id,
            'post_author' => !empty($data['wp_user_id']) ? (int) $data['wp_user_id'] : 0,
        ]);

        if (is_wp_error($post_id)) {
            return $post_id;
        }

        $attachments = $this->handle_attachments($request);
        if (is_wp_error($attachments)) {
            return $attachments;
        }

        $this->save_submission_meta($post_id, $data, $attachments);

        return rest_ensure_response([
            'success'     => true,
            'post_id'     => $post_id,
            'submission'  => $this->format_submission($post_id),
        ]);
    }

    public function update_submission(\WP_REST_Request $request)
    {
        $post_id = absint($request['id']);
        $post = get_post($post_id);

        if (!$post || $post->post_type !== 'rental-submissions') {
            return new \WP_Error(
                'members_weeks_not_found',
                'Submission not found.',
                ['status' => 404]
            );
        }

        $data = $this->validate_submission_payload($request);

        if (is_wp_error($data)) {
            return $data;
        }

        $update = wp_update_post([
            'ID'         => $post_id,
            'post_title' => $data['unit_name'] . ' | ' . $data['week_start'] . ' - ' . $data['week_end'],
        ], true);

        if (is_wp_error($update)) {
            return $update;
        }

        $attachments = $this->handle_attachments($request, $post_id);
        if (is_wp_error($attachments)) {
            return $attachments;
        }

        $this->save_submission_meta($post_id, $data, $attachments);

        return rest_ensure_response([
            'success'    => true,
            'post_id'    => $post_id,
            'submission' => $this->format_submission($post_id),
        ]);
    }

    private function validate_submission_payload(\WP_REST_Request $request)
    {
        $user_email      = sanitize_email((string) $request->get_param('user_email'));
        $wp_user_id      = absint($request->get_param('wp_user_id'));
        $guest_name      = sanitize_text_field((string) $request->get_param('guest_name'));
        $initials        = sanitize_text_field((string) $request->get_param('initials'));
        $unit_name       = sanitize_text_field((string) $request->get_param('unit_name'));
        $wp_property_id  = $request->get_param('wp_property_id') !== null ? absint($request->get_param('wp_property_id')) : null;
        $wp_room_type_id = $request->get_param('wp_room_type_id') !== null ? absint($request->get_param('wp_room_type_id')) : null;
        $week_start      = sanitize_text_field((string) $request->get_param('week_start'));
        $week_end        = sanitize_text_field((string) $request->get_param('week_end'));
        $privileges      = $request->get_param('privileges');

        if (!$user_email) {
            return new \WP_Error('members_weeks_invalid_email', 'user_email is required.', ['status' => 422]);
        }

        if ($guest_name === '') {
            return new \WP_Error('members_weeks_invalid_name', 'guest_name is required.', ['status' => 422]);
        }

        if ($initials === '') {
            return new \WP_Error('members_weeks_invalid_initials', 'initials are required.', ['status' => 422]);
        }

        if ($unit_name === '') {
            return new \WP_Error('members_weeks_invalid_unit', 'unit_name is required.', ['status' => 422]);
        }

        if (!$this->is_valid_date($week_start) || !$this->is_valid_date($week_end)) {
            return new \WP_Error('members_weeks_invalid_dates', 'week_start and week_end must be valid dates in Y-m-d format.', ['status' => 422]);
        }

        if (strtotime($week_end) < strtotime($week_start)) {
            return new \WP_Error('members_weeks_invalid_range', 'week_end must be after or equal to week_start.', ['status' => 422]);
        }

        if (is_string($privileges)) {
            $decoded = json_decode($privileges, true);
            $privileges = is_array($decoded) ? $decoded : ($privileges !== '' ? [$privileges] : []);
        }

        if (!is_array($privileges)) {
            $privileges = [];
        }

        $privileges = array_values(array_filter(array_map(function ($v) {
            return sanitize_text_field((string) $v);
        }, $privileges)));

        return [
            'user_email'      => $user_email,
            'wp_user_id'      => $wp_user_id ?: null,
            'guest_name'      => $guest_name,
            'initials'        => $initials,
            'unit_name'       => $unit_name,
            'wp_property_id'  => $wp_property_id ?: null,
            'wp_room_type_id' => $wp_room_type_id ?: null,
            'week_start'      => $week_start,
            'week_end'        => $week_end,
            'privileges'      => $privileges,
        ];
    }

    private function handle_attachments(\WP_REST_Request $request, int $post_id = 0)
    {
        $result = [
            'confirmation_attachment_id'  => null,
            'confirmation_attachment_url' => null,
            'amenities_attachment_id'     => null,
            'amenities_attachment_url'    => null,
        ];

        $files = $request->get_file_params();

        if (!empty($files['confirmation_file'])) {
            $uploaded = Members_Weeks_Media::sideload_from_request_file($files['confirmation_file']);
            if (is_wp_error($uploaded)) return $uploaded;

            $result['confirmation_attachment_id']  = $uploaded['id'];
            $result['confirmation_attachment_url'] = $uploaded['url'];
        } elseif ($post_id) {
            $existing_id = get_post_meta($post_id, 'confirmation-of-reservation', true);
            $result['confirmation_attachment_id']  = $existing_id ? absint($existing_id) : null;
            $result['confirmation_attachment_url'] = $existing_id ? wp_get_attachment_url($existing_id) : null;
        }

        if (!empty($files['amenities_file'])) {
            $uploaded = Members_Weeks_Media::sideload_from_request_file($files['amenities_file']);
            if (is_wp_error($uploaded)) return $uploaded;

            $result['amenities_attachment_id']  = $uploaded['id'];
            $result['amenities_attachment_url'] = $uploaded['url'];
        } elseif ($post_id) {
            $existing_id = get_post_meta($post_id, 'promotional-amenity-privileges', true);
            $result['amenities_attachment_id']  = $existing_id ? absint($existing_id) : null;
            $result['amenities_attachment_url'] = $existing_id ? wp_get_attachment_url($existing_id) : null;
        }

        return $result;
    }

    private function save_submission_meta(int $post_id, array $data, array $attachments)
    {

        $start_ts = strtotime($data['week_start'] . ' 00:00:00');
        $end_ts   = strtotime($data['week_end'] . ' 00:00:00');

    
        update_post_meta($post_id, 'email', $data['user_email']);
        update_post_meta($post_id, 'wp_user_id', $data['wp_user_id']);
        update_post_meta($post_id, 'nombre', $data['guest_name']);
        update_post_meta($post_id, 'initials', $data['initials']);
        update_post_meta($post_id, 'unit-for-rent', $data['unit_name']);

        update_post_meta($post_id, 'date-of-start', $start_ts);
        update_post_meta($post_id, 'date-of-end', $end_ts);

        update_post_meta($post_id, 'privileges', $data['privileges']);

        update_post_meta($post_id, 'property_id', $data['wp_property_id']);
        update_post_meta($post_id, '_property_id', $data['wp_property_id']);

        update_post_meta($post_id, 'room_type_id', $data['wp_room_type_id']);
        update_post_meta($post_id, '_room_type_id', $data['wp_room_type_id']);

        if (!empty($attachments['confirmation_attachment_id'])) {
            update_post_meta($post_id, 'confirmation-of-reservation', $attachments['confirmation_attachment_id']);
        }

        if (!empty($attachments['amenities_attachment_id'])) {
            update_post_meta($post_id, 'promotional-amenity-privileges', $attachments['amenities_attachment_id']);
        }

        // Nullable por ahora
        if (get_post_meta($post_id, 'semana_status', true) === '') {
            update_post_meta($post_id, 'semana_status', null);
        }

        if (get_post_meta($post_id, 'reserva_status_code', true) === '') {
            update_post_meta($post_id, 'reserva_status_code', null);
        }

        if (get_post_meta($post_id, 'priority-color', true) === '') {
            update_post_meta($post_id, 'priority-color', null);
        }
    }

    private function format_submission(int $post_id)
    {
        $confirmation_id = get_post_meta($post_id, 'confirmation-of-reservation', true);
        $amenities_id    = get_post_meta($post_id, 'promotional-amenity-privileges', true);

        $start_raw = get_post_meta($post_id, 'date-of-start', true);
        $end_raw   = get_post_meta($post_id, 'date-of-end', true);

        

        $privileges = get_post_meta($post_id, 'privileges', true);

        if (is_array($privileges)) {
            $decoded = $privileges;
        } else {
            $decoded = json_decode((string) $privileges, true);
            if (!is_array($decoded)) {
                $decoded = $privileges ? [(string) $privileges] : [];
            }
        }

        return [
            'post_id' => $post_id,
            'email' => get_post_meta($post_id, 'email', true),
            'wp_user_id' => get_post_meta($post_id, 'wp_user_id', true),
            'guest_name' => get_post_meta($post_id, 'nombre', true),
            'initials' => get_post_meta($post_id, 'initials', true),
            'unit_name' => get_post_meta($post_id, 'unit-for-rent', true),
            'week_start' => $start_raw ? date('Y-m-d', (int) $start_raw) : null,
            'week_end' => $end_raw ? date('Y-m-d', (int) $end_raw) : null,
            'privileges' => $decoded,
            'wp_property_id' => get_post_meta($post_id, 'property_id', true),
            'wp_room_type_id' => get_post_meta($post_id, 'room_type_id', true),
            'semana_status' => get_post_meta($post_id, 'semana_status', true),
            'reserva_status_code' => get_post_meta($post_id, 'reserva_status_code', true),
            'priority_color' => get_post_meta($post_id, 'priority-color', true),
            'confirmation_attachment_id' => $confirmation_id ? absint($confirmation_id) : null,
            'confirmation_attachment_url' => $confirmation_id ? wp_get_attachment_url($confirmation_id) : null,
            'amenities_attachment_id' => $amenities_id ? absint($amenities_id) : null,
            'amenities_attachment_url' => $amenities_id ? wp_get_attachment_url($amenities_id) : null,
        ];
    }

    private function is_valid_date(string $value): bool
    {
        $dt = \DateTime::createFromFormat('Y-m-d', $value);
        return $dt && $dt->format('Y-m-d') === $value;
    }

    public function delete_submission(\WP_REST_Request $request)
    {
        $post_id = absint($request['id']);
        $post = get_post($post_id);

        if (!$post || $post->post_type !== 'rental-submissions') {
            return new \WP_Error(
                'members_weeks_not_found',
                'Submission not found.',
                ['status' => 404]
            );
        }

        $deleted = wp_delete_post($post_id, true);

        if (!$deleted) {
            return new \WP_Error(
                'members_weeks_delete_failed',
                'Failed to delete submission.',
                ['status' => 500]
            );
        }

        return rest_ensure_response([
            'success' => true,
            'deleted_post_id' => $post_id,
        ]);
    }

 //esto del agreement se metío acá por flojera de crear otro plugin y otro sevicio :V
    public function get_agreement(\WP_REST_Request $request)
    {
        $email = sanitize_email((string) $request->get_param('email'));

        if (!$email) {
            return new \WP_Error(
                'members_weeks_invalid_email',
                'Email is required.',
                ['status' => 422]
            );
        }

        $posts = get_posts([
            'post_type'      => 'agreement',
            'post_status'    => ['publish', 'private'],
            'posts_per_page' => 1,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'meta_query'     => [
                [
                    'key'   => 'email_owner',
                    'value' => $email,
                ],
            ],
        ]);

        if (empty($posts)) {
            return rest_ensure_response([
                'success' => true,
                'item' => null,
            ]);
        }

        $post = $posts[0];
        $fullContactName = get_post_meta($post->ID, 'full-name-contact', true);

        return rest_ensure_response([
            'success' => true,
            'item' => [
                'post_id' => $post->ID,
                'title' => get_the_title($post->ID),
                'permalink' => get_permalink($post->ID),
                'created_at' => get_post_field('post_date', $post->ID),
                'full_name_contact' => $fullContactName ?: '',
            ],
        ]);
    }

    public function get_preferred_rental_request(\WP_REST_Request $request)
    {
        if ((bool) $request->get_param('all')) {
            return $this->list_preferred_rental_requests($request);
        }

        $email = sanitize_email((string) $request->get_param('email'));
        $wp_user_id = absint($request->get_param('wp_user_id'));
        $after = sanitize_text_field((string) $request->get_param('after'));

        if ($after === '') {
            $after = '2024-09-15';
        }

        $date_query = [];
        if ($after !== 'none') {
            $date_query = [
                [
                    'column' => 'post_date',
                    'after'  => $after,
                ],
            ];
        }

        if (!$wp_user_id && !$email) {
            return new \WP_Error(
                'members_weeks_invalid_request',
                'wp_user_id or email is required.',
                ['status' => 422]
            );
        }

        $posts = [];

        if ($wp_user_id) {
            $args = [
                'author'         => $wp_user_id,
                'post_type'      => 'preferred-rentals',
                'posts_per_page' => 1,
                'order'          => 'DESC',
                'post_status'    => ['publish', 'private', 'draft', 'pending'],
            ];

            if (!empty($date_query)) {
                $args['date_query'] = $date_query;
            }

            $posts = get_posts($args);
        }

        // fallback por email si por author no encontró nada
        if (empty($posts) && $email) {
            $wp_user = get_user_by('email', $email);

            if ($wp_user && !empty($wp_user->ID)) {
                $args = [
                    'author'         => (int) $wp_user->ID,
                    'post_type'      => 'preferred-rentals',
                    'posts_per_page' => 1,
                    'order'          => 'DESC',
                    'post_status'    => ['publish', 'private', 'draft', 'pending'],
                ];

                if (!empty($date_query)) {
                    $args['date_query'] = $date_query;
                }

                $posts = get_posts($args);
            }
        }

        // último fallback por el meta legacy email
        if (empty($posts) && $email) {
            $args = [
                'post_type'      => 'preferred-rentals',
                'posts_per_page' => 1,
                'order'          => 'DESC',
                'post_status'    => ['publish', 'private', 'draft', 'pending'],
                'meta_query'     => [
                    [
                        'key'   => 'email',
                        'value' => $email,
                    ],
                ],
            ];

            if (!empty($date_query)) {
                $args['date_query'] = $date_query;
            }

            $posts = get_posts($args);
        }

        if (empty($posts)) {
            return rest_ensure_response([
                'success' => true,
                'item' => null,
            ]);
        }

        return rest_ensure_response([
            'success' => true,
            'item' => $this->format_preferred_rental_request($posts[0]->ID),
        ]);
    }

    public function list_preferred_rental_requests(\WP_REST_Request $request)
    {
        $per_page = absint($request->get_param('per_page'));
        $page = absint($request->get_param('page'));
        $after = sanitize_text_field((string) $request->get_param('after'));

        if ($per_page < 1) {
            $per_page = 200;
        }

        $per_page = min($per_page, 500);
        $page = max($page, 1);

        if ($after === '') {
            $after = '2024-09-15';
        }

        $args = [
            'post_type'      => 'preferred-rentals',
            'post_status'    => ['publish', 'private', 'draft', 'pending'],
            'posts_per_page' => $per_page,
            'paged'          => $page,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'fields'         => 'ids',
        ];

        if ($after !== 'none') {
            $args['date_query'] = [
                [
                    'column' => 'post_date',
                    'after'  => $after,
                ],
            ];
        }

        $query = new \WP_Query($args);

        $items = array_map(function ($post_id) {
            return $this->format_preferred_rental_request((int) $post_id);
        }, $query->posts);

        return rest_ensure_response([
            'success'  => true,
            'items'    => $items,
            'total'    => (int) $query->found_posts,
            'page'     => $page,
            'per_page' => $per_page,
            'has_more' => $page < (int) $query->max_num_pages,
        ]);
    }

    public function save_preferred_rental_request(\WP_REST_Request $request)
    {
        $data = $this->validate_preferred_rental_request_payload($request);

        if (is_wp_error($data)) {
            return $data;
        }

        $existing = [];

        if (!empty($data['wp_user_id'])) {
            $existing = get_posts([
                'author'         => $data['wp_user_id'],
                'post_type'      => 'preferred-rentals',
                'posts_per_page' => 1,
                'order'          => 'DESC',
                'post_status'    => ['publish', 'private', 'draft', 'pending'],
                'date_query'     => [
                    [
                        'column' => 'post_date',
                        'after'  => '2024-09-15',
                    ],
                ],
            ]);
        }

        if (empty($existing) && !empty($data['user_email'])) {
            $existing = get_posts([
                'post_type'      => 'preferred-rentals',
                'posts_per_page' => 1,
                'order'          => 'DESC',
                'post_status'    => ['publish', 'private', 'draft', 'pending'],
                'date_query'     => [
                    [
                        'column' => 'post_date',
                        'after'  => '2024-09-15',
                    ],
                ],
                'meta_query'     => [
                    [
                        'key'   => 'email',
                        'value' => $data['user_email'],
                    ],
                ],
            ]);
        }

        $post_id = null;
        $titleBase = preg_replace('/\s+/', ' ', trim($data['name'])) . '_' . $data['user_email'];

        if (!empty($existing)) {
            $post_id = $existing[0]->ID;

            $updated = wp_update_post([
                'ID'          => $post_id,
                'post_title'  => 'PRR_R' . $titleBase,
                'post_author' => $data['wp_user_id'] ?: 0,
            ], true);

            if (is_wp_error($updated)) {
                return $updated;
            }
        } else {
            $post_id = wp_insert_post([
                'post_title'  => 'PRR_' . $titleBase,
                'post_status' => 'publish',
                'post_author' => $data['wp_user_id'] ?: 0,
                'post_type'   => 'preferred-rentals',
            ], true);

            if (is_wp_error($post_id)) {
                return $post_id;
            }
        }

        $this->save_preferred_rental_request_meta($post_id, $data);

        /*
        // Dejar comentado hasta producción real
        $to = $data['user_email'];
        $subject = 'VIMA Preferred Rental Requests';
        $message = '...';
        $headers = ['Content-Type: text/html; charset=UTF-8'];
        wp_mail($to, $subject, $message, $headers);
        */

        return rest_ensure_response([
            'success' => true,
            'post_id' => $post_id,
            'item' => $this->format_preferred_rental_request($post_id),
        ]);
    }

    private function validate_preferred_rental_request_payload(\WP_REST_Request $request)
    {
        $user_email = sanitize_email((string) $request->get_param('user_email'));
        $wp_user_id = absint($request->get_param('wp_user_id'));
        $name = sanitize_text_field((string) $request->get_param('name'));
        $initials = sanitize_text_field((string) $request->get_param('initials'));
        $number_of_weeks_available = absint($request->get_param('number_of_weeks_available'));
        $units = $request->get_param('units');

        if (!$user_email) {
            return new \WP_Error('members_weeks_invalid_email', 'user_email is required.', ['status' => 422]);
        }

        if ($name === '') {
            $name = $user_email;
        }

        if ($initials === '') {
            $initials = 'NA';
        }

        if ($number_of_weeks_available < 1) {
            return new \WP_Error('members_weeks_invalid_weeks', 'number_of_weeks_available must be at least 1.', ['status' => 422]);
        }

        if (is_string($units)) {
            $decoded = json_decode($units, true);
            $units = is_array($decoded) ? $decoded : [];
        }

        if (!is_array($units) || empty($units)) {
            return new \WP_Error('members_weeks_invalid_units', 'At least one unit is required.', ['status' => 422]);
        }

        $units = array_values(array_filter(array_map(function ($v) {
            return sanitize_text_field((string) $v);
        }, $units)));

        if (empty($units)) {
            return new \WP_Error('members_weeks_invalid_units', 'At least one valid unit is required.', ['status' => 422]);
        }

        return [
            'user_email' => $user_email,
            'wp_user_id' => $wp_user_id ?: null,
            'name' => $name,
            'initials' => $initials,
            'number_of_weeks_available' => $number_of_weeks_available,
            'units' => $units,
        ];
    }

    private function save_preferred_rental_request_meta(int $post_id, array $data)
    {
        $unitMap = [];
        foreach ($data['units'] as $unit) {
            $unitMap[$unit] = 'true';
        }

        update_post_meta($post_id, 'email', $data['user_email']);
        update_post_meta($post_id, 'wp_user_id', $data['wp_user_id']);
        update_post_meta($post_id, 'name', $data['name']);
        update_post_meta($post_id, 'nombre', $data['name']);
        update_post_meta($post_id, 'initials', $data['initials']);
        update_post_meta($post_id, 'number-of-weeks-available', $data['number_of_weeks_available']);

        // Legacy raro pero necesario
        update_post_meta($post_id, 'unit', $unitMap);
        update_post_meta($post_id, 'unit-for-rent', $data['units']);

        wp_set_object_terms($post_id, $data['units'], 'prr-selected-units', false);

        if (get_post_meta($post_id, 'semana_status', true) === '') {
            update_post_meta($post_id, 'semana_status', null);
        }
    }

    private function format_preferred_rental_request(int $post_id)
    {
        $unitForRent = get_post_meta($post_id, 'unit-for-rent', true);
        $unitMap = get_post_meta($post_id, 'unit', true);
        $wpUserId = absint(get_post_field('post_author', $post_id));
        $metaWpUserId = absint(get_post_meta($post_id, 'wp_user_id', true));
        $resolvedWpUserId = $wpUserId ?: ($metaWpUserId ?: null);
        $wpUser = $resolvedWpUserId ? get_user_by('id', $resolvedWpUserId) : false;
        $wpUserEmail = $wpUser && !empty($wpUser->user_email) ? (string) $wpUser->user_email : '';
        $wpDisplayName = $wpUser && !empty($wpUser->display_name) ? (string) $wpUser->display_name : '';
        $metaEmail = get_post_meta($post_id, 'email', true);
        $metaName = get_post_meta($post_id, 'name', true) ?: get_post_meta($post_id, 'nombre', true);
        $email = $wpUserEmail ?: $metaEmail;
        $phone = $this->resolve_preferred_rental_phone($post_id, $wpUser, $email);

        if (!is_array($unitForRent)) {
            $unitForRent = [];
        }

        if (empty($unitForRent) && is_array($unitMap)) {
            $unitForRent = array_keys($unitMap);
        }

        return [
            'post_id' => $post_id,
            'wp_user_id' => $resolvedWpUserId,
            'email' => $email,
            'phone' => $phone ?: null,
            'name' => $metaName ?: ($wpDisplayName ?: $email),
            'initials' => get_post_meta($post_id, 'initials', true),
            'number_of_weeks_available' => (int) get_post_meta($post_id, 'number-of-weeks-available', true),
            'units' => array_values($unitForRent),
            'semana_status' => get_post_meta($post_id, 'semana_status', true),
        ];
    }

    private function resolve_preferred_rental_phone(int $post_id, $wp_user, string $email): string
    {
        $phone = $this->first_post_meta($post_id, ['phone', 'user_phone', 'billing_phone', 'telefono']);

        if ($phone !== '') {
            return $phone;
        }

        if (!$wp_user && $email !== '') {
            $wp_user = get_user_by('email', $email);
        }

        if ($wp_user && !empty($wp_user->ID)) {
            return $this->first_user_meta((int) $wp_user->ID, ['billing_phone', 'phone', 'user_phone', 'telefono']);
        }

        return '';
    }

    private function first_post_meta(int $post_id, array $keys): string
    {
        foreach ($keys as $key) {
            $value = $this->normalize_phone_value(get_post_meta($post_id, $key, true));

            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    private function first_user_meta(int $user_id, array $keys): string
    {
        foreach ($keys as $key) {
            $value = $this->normalize_phone_value(get_user_meta($user_id, $key, true));

            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    private function normalize_phone_value($value): string
    {
        if (is_array($value)) {
            $value = reset($value);
        }

        return substr(trim(sanitize_text_field((string) $value)), 0, 50);
    }

}
