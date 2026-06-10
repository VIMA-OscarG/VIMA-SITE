<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function pmue_wpae_user_available_sections($available_sections)
{
    XmlExportUser::$is_export_shop_customer or $available_sections['other']['title'] = esc_html__('Advanced', 'export-wp-users-xml-csv');
    XmlExportUser::$is_export_shop_customer or $available_sections['cf']['title'] = esc_html__('User Meta', 'export-wp-users-xml-csv');

    return $available_sections;
}