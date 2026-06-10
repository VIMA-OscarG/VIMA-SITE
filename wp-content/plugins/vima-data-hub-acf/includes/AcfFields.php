<?php
if (!defined('ABSPATH')) {
  exit;
}

class Vima_DH_Acf_Fields {

  /**
   * Registra field groups con acf_add_local_field_group (ACF en código).
   * Requiere ACF Pro para repeaters.
   */
  public static function register_field_groups() : void {
    if (!function_exists('acf_add_local_field_group')) {
      return;
    }

    // Repeater requiere ACF Pro.
    if (!function_exists('acf_add_local_field')) {
      // Fallback: si ACF está activo pero algo raro pasa, no revientes.
      return;
    }

    self::register_channel_fields();
    self::register_season_fields();
    self::register_resort_fields();
    self::register_destination_fields();
    self::register_property_fields();
  }

  private static function location(string $post_type) : array {
    return [
      [
        [
          'param' => 'post_type',
          'operator' => '==',
          'value' => $post_type,
        ],
      ],
    ];
  }

  private static function register_channel_fields() : void {
    acf_add_local_field_group([
      'key' => 'group_vima_channel',
      'title' => 'VIMA Channel',
      'fields' => [
        [
          'key' => 'field_vima_channel_key',
          'label' => 'Channel Key',
          'name' => 'channel_key',
          'type' => 'text',
          'instructions' => 'Identificador único (ej: airbnb, vrbo, booking, plr).',
          'required' => 1,
          'wrapper' => ['width' => '50'],
        ],
        [
          'key' => 'field_vima_channel_label',
          'label' => 'Label',
          'name' => 'label',
          'type' => 'text',
          'instructions' => 'Nombre visible (ej: Airbnb).',
          'required' => 1,
          'wrapper' => ['width' => '50'],
        ],
        [
          'key' => 'field_vima_channel_logo',
          'label' => 'Logo',
          'name' => 'logo',
          'type' => 'image',
          'instructions' => 'Logo del proveedor (Media Library).',
          'required' => 0,
          'return_format' => 'id',     // guardamos ID para flexibilidad
          'preview_size' => 'thumbnail',
          'library' => 'all',
          'wrapper' => ['width' => '50'],
        ],
        [
          'key' => 'field_vima_channel_preview_mode',
          'label' => 'Preview Mode',
          'name' => 'preview_mode',
          'type' => 'select',
          'instructions' => 'Cómo se muestra el preview en el plugin.',
          'required' => 0,
          'choices' => [
            'iframe' => 'iframe',
            'redirect' => 'redirect',
            'none' => 'none',
          ],
          'default_value' => 'iframe',
          'allow_null' => 0,
          'ui' => 1,
          'wrapper' => ['width' => '50'],
        ],
        [
          'key' => 'field_vima_channel_preview_description',
          'label' => 'Preview Description',
          'name' => 'preview_description',
          'type' => 'textarea',
          'instructions' => 'Texto que explica el preview (si aplica).',
          'required' => 0,
          'rows' => 3,
        ],
      ],
      'location' => self::location('vima_channel'),
      'position' => 'normal',
      'style' => 'default',
      'label_placement' => 'top',
      'instruction_placement' => 'label',
      'active' => true,
    ]);
  }

  private static function register_season_fields() : void {
    acf_add_local_field_group([
      'key' => 'group_vima_season',
      'title' => 'VIMA Season',
      'fields' => [
        [
          'key' => 'field_vima_season_key',
          'label' => 'Season Key',
          'name' => 'season_key',
          'type' => 'text',
          'instructions' => 'Identificador único (ej: winter_2026, high, low).',
          'required' => 1,
          'wrapper' => ['width' => '50'],
        ],
        [
          'key' => 'field_vima_season_label',
          'label' => 'Label',
          'name' => 'label',
          'type' => 'text',
          'instructions' => 'Nombre visible (ej: Winter 2026).',
          'required' => 1,
          'wrapper' => ['width' => '50'],
        ],
      ],
      'location' => self::location('vima_season'),
      'position' => 'normal',
      'style' => 'default',
      'label_placement' => 'top',
      'instruction_placement' => 'label',
      'active' => true,
    ]);
  }

  private static function register_destination_fields() : void {
    acf_add_local_field_group([
      'key' => 'group_vima_destination',
      'title' => 'VIMA Destination',
      'fields' => [
        [
          'key' => 'field_vima_destination_lat',
          'label' => 'Latitude',
          'name' => 'lat',
          'type' => 'number',
          'required' => 0,
          'step' => '0.0000001',
          'wrapper' => ['width' => '50'],
        ],
        [
          'key' => 'field_vima_destination_lng',
          'label' => 'Longitude',
          'name' => 'lng',
          'type' => 'number',
          'required' => 0,
          'step' => '0.0000001',
          'wrapper' => ['width' => '50'],
        ],
      ],
      'location' => self::location('vima_destination'),
      'position' => 'normal',
      'style' => 'default',
      'label_placement' => 'top',
      'instruction_placement' => 'label',
      'active' => true,
    ]);
  }

  private static function register_resort_fields() : void {
    acf_add_local_field_group([
      'key' => 'group_vima_resort',
      'title' => 'VIMA Resort',
      'fields' => [
        [
          'key' => 'field_vima_resort_destination',
          'label' => 'Destination',
          'name' => 'destination',
          'type' => 'post_object',
          'post_type' => ['vima_destination'],
          'return_format' => 'id',
          'required' => 1,
          'ui' => 1,
          'multiple' => 0,
          'wrapper' => ['width' => '100'],
        ],
      ],
      'location' => self::location('vima_resort'),
      'position' => 'normal',
      'style' => 'default',
      'label_placement' => 'top',
      'instruction_placement' => 'label',
      'active' => true,
    ]);
  }

  private static function register_property_fields() : void {
    acf_add_local_field_group([
      'key' => 'group_vima_property',
      'title' => 'VIMA Property',
      'fields' => [
        // Relaciones
        [
          'key' => 'field_vima_property_destination',
          'label' => 'Destination',
          'name' => 'destination',
          'type' => 'post_object',
          'post_type' => ['vima_destination'],
          'return_format' => 'id',
          'required' => 1,
          'ui' => 1,
          'multiple' => 0,
          'wrapper' => ['width' => '50'],
        ],
        [
          'key' => 'field_vima_property_resort',
          'label' => 'Resort',
          'name' => 'resort',
          'type' => 'post_object',
          'post_type' => ['vima_resort'],
          'return_format' => 'id',
          'required' => 1,
          'ui' => 1,
          'multiple' => 0,
          'wrapper' => ['width' => '50'],
        ],

        // Thumb
        [
          'key' => 'field_vima_property_thumb',
          'label' => 'Thumbnail',
          'name' => 'thumb',
          'type' => 'image',
          'instructions' => 'Imagen miniatura de la propiedad.',
          'required' => 0,
          'return_format' => 'id',
          'preview_size' => 'thumbnail',
          'library' => 'all',
          'wrapper' => ['width' => '100'],
        ],

        // IDs externos
        [
          'key' => 'field_vima_property_external_ids_tab',
          'label' => 'External IDs',
          'name' => '',
          'type' => 'tab',
          'placement' => 'top',
        ],
        [
          'key' => 'field_vima_property_inventory_property_id',
          'label' => 'Inventory Property ID',
          'name' => 'inventory_property_id',
          'type' => 'text',
          'required' => 0,
          'wrapper' => ['width' => '33'],
        ],
        [
          'key' => 'field_vima_property_listing_popup_id',
          'label' => 'Listing Popup ID',
          'name' => 'listing_popup_id',
          'type' => 'text',
          'required' => 0,
          'wrapper' => ['width' => '33'],
        ],
        [
          'key' => 'field_vima_property_rates_popup_id',
          'label' => 'Rates Popup ID',
          'name' => 'rates_popup_id',
          'type' => 'text',
          'required' => 0,
          'wrapper' => ['width' => '33'],
        ],

        // Channels + URLs
        [
          'key' => 'field_vima_property_channels_tab',
          'label' => 'Channels',
          'name' => '',
          'type' => 'tab',
          'placement' => 'top',
        ],
        [
          'key' => 'field_vima_property_channels',
          'label' => 'Channels',
          'name' => 'channels',
          'type' => 'repeater',
          'instructions' => 'Asigna proveedores a esta propiedad y agrega 1 o más URLs por proveedor.',
          'required' => 0,
          'min' => 0,
          'layout' => 'row',
          'button_label' => 'Add Channel',
          'sub_fields' => [
            [
              'key' => 'field_vima_property_channels_channel',
              'label' => 'Channel',
              'name' => 'channel',
              'type' => 'post_object',
              'post_type' => ['vima_channel'],
              'return_format' => 'id',
              'required' => 1,
              'ui' => 1,
              'multiple' => 0,
              'wrapper' => ['width' => '30'],
            ],
            [
              'key' => 'field_vima_property_channels_urls',
              'label' => 'URLs',
              'name' => 'urls',
              'type' => 'repeater',
              'required' => 1,
              'min' => 1,
              'layout' => 'table',
              'button_label' => 'Add URL',
              'wrapper' => ['width' => '70'],
              'sub_fields' => [
                [
                  'key' => 'field_vima_property_channels_urls_url',
                  'label' => 'URL',
                  'name' => 'url',
                  'type' => 'url',
                  'required' => 1,
                ],
              ],
            ],
          ],
        ],

        // Rates
        [
          'key' => 'field_vima_property_rates_tab',
          'label' => 'Rates',
          'name' => '',
          'type' => 'tab',
          'placement' => 'top',
        ],
        [
          'key' => 'field_vima_property_rates',
          'label' => 'Rates',
          'name' => 'rates',
          'type' => 'repeater',
          'instructions' => 'Rates por temporada.',
          'required' => 0,
          'min' => 0,
          'layout' => 'table',
          'button_label' => 'Add Rate',
          'sub_fields' => [
            [
              'key' => 'field_vima_property_rates_season',
              'label' => 'Season',
              'name' => 'season',
              'type' => 'post_object',
              'post_type' => ['vima_season'],
              'return_format' => 'id',
              'required' => 1,
              'ui' => 1,
            ],
            [
              'key' => 'field_vima_property_rates_amount',
              'label' => 'Amount',
              'name' => 'amount',
              'type' => 'number',
              'required' => 0,
              'step' => '0.01',
              'min' => 0,
              'wrapper' => ['width' => '50'],
            ],
            [
              'key' => 'field_vima_property_rates_currency',
              'label' => 'Currency',
              'name' => 'currency',
              'type' => 'text',
              'required' => 0,
              'default_value' => 'USD',
              'maxlength' => 3,
              'wrapper' => ['width' => '50'],
            ],
          ],
        ],

        // Seasonality
        [
          'key' => 'field_vima_property_seasonality_tab',
          'label' => 'Seasonality',
          'name' => '',
          'type' => 'tab',
          'placement' => 'top',
        ],
        [
          'key' => 'field_vima_property_seasonality',
          'label' => 'Seasonality',
          'name' => 'seasonality',
          'type' => 'repeater',
          'instructions' => 'Rangos de fechas por temporada.',
          'required' => 0,
          'min' => 0,
          'layout' => 'row',
          'button_label' => 'Add Seasonality',
          'sub_fields' => [
            [
              'key' => 'field_vima_property_seasonality_season',
              'label' => 'Season',
              'name' => 'season',
              'type' => 'post_object',
              'post_type' => ['vima_season'],
              'return_format' => 'id',
              'required' => 1,
              'ui' => 1,
              'wrapper' => ['width' => '30'],
            ],
            [
              'key' => 'field_vima_property_seasonality_ranges',
              'label' => 'Ranges',
              'name' => 'ranges',
              'type' => 'repeater',
              'required' => 1,
              'min' => 1,
              'layout' => 'table',
              'button_label' => 'Add Range',
              'wrapper' => ['width' => '70'],
              'sub_fields' => [
                [
                  'key' => 'field_vima_property_seasonality_ranges_start',
                  'label' => 'Start Date',
                  'name' => 'start_date',
                  'type' => 'date_picker',
                  'required' => 1,
                  'display_format' => 'Y-m-d',
                  'return_format' => 'Y-m-d',
                  'first_day' => 1,
                ],
                [
                  'key' => 'field_vima_property_seasonality_ranges_end',
                  'label' => 'End Date',
                  'name' => 'end_date',
                  'type' => 'date_picker',
                  'required' => 1,
                  'display_format' => 'Y-m-d',
                  'return_format' => 'Y-m-d',
                  'first_day' => 1,
                ],
              ],
            ],
          ],
        ],
      ],
      'location' => self::location('vima_property'),
      'position' => 'normal',
      'style' => 'default',
      'label_placement' => 'top',
      'instruction_placement' => 'label',
      'active' => true,
    ]);
  }
}
