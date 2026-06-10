<?php
namespace JupiterX_Core\Popup\Triggers;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class User Type.
 *
 * @since 3.7.0
 */
class User_Type extends Triggers_Base {
	/**
	 * Get trigger name.
	 *
	 * @since 3.7.0
	 * @return string
	 */
	public function get_name() {
		return 'user_type';
	}

	/**
	 * Get trigger label.
	 *
	 * @since 3.7.0
	 * @return string
	 */
	public function get_label() {
		return esc_html__( 'User Type', 'jupiterx-core' );
	}

	/**
	 * Get trigger operators.
	 *
	 * @since 3.7.0
	 * @return array
	 */
	public function operators() {
		return [
			'is',
			'is-not',
		];
	}

	/**
	 * Get trigger options.
	 *
	 * @since 3.7.0
	 * @return array
	 */
	public function get_options() {
		$types = [
			'logged_in' => esc_html__( 'Logged in User', 'jupiterx-core' ),
			'first_time' => esc_html__( 'First Time Visitor', 'jupiterx-core' ),
			'repeat' => esc_html__( 'Repeat Visitor', 'jupiterx-core' ),
		];

		$options = [];

		foreach ( $types as $key => $type ) {
			$options[] = [
				'id' => $key,
				'name' => $type,
			];
		}

		return $options;
	}

	/**
	 * Get trigger control.
	 *
	 * @since 3.7.0
	 * @return array
	 */
	public function add_control() {
		return [
			'type' => 'drop-down',
		];
	}

	/**
	 * Operator validation.
	 *
	 * @since 3.7.0
	 * @param mixed $triggers triggers value.
	 */
	public function is_valid( $triggers ) {
		$user_type = is_user_logged_in() ? 'logged_in' : 'first_time';

		if ( 'is' === $triggers['user_type']['operator'] && $user_type === $triggers['user_type']['control'] ) {
			return true;
		}

		if ( 'is-not' === $triggers['user_type']['operator'] && $user_type !== $triggers['user_type']['control'] ) {
			return true;
		}

		return false;
	}
}
