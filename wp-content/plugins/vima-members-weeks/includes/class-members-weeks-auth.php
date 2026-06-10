<?php

if (!defined('ABSPATH')) exit;

class Members_Weeks_Auth
{
    public static function check_request(\WP_REST_Request $request)
    {
        $provided = $request->get_header('x-members-weeks-secret');
        $expected = defined('MEMBERS_WEEKS_SHARED_SECRET') ? MEMBERS_WEEKS_SHARED_SECRET : '';

        if (!$expected || !$provided || !hash_equals($expected, $provided)) {
            return new \WP_Error(
                'members_weeks_forbidden',
                'Invalid shared secret.',
                ['status' => 403]
            );
        }

        return true;
    }
}
