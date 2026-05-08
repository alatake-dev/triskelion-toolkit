<?php
/**
 * Wrapper for WordPress Menu functions.
 *
 * @package    Triskelion\TriskelionToolkit
 * @subpackage Core\Bridge
 * @since      1.0.0
 */

namespace Triskelion\TriskelionToolkit\Core\Bridge;

/**
 * Class WpMenu
 *
 * Handles menu and submenu registration in the WordPress Dashboard.
 *
 * @package Triskelion\TriskelionToolkit\Core\Bridge
 */
class WpMenu extends AbstractWpBridge {

	/**
	 * Adds a top-level menu page.
	 * FF Logic: Returns the slug as the hook_suffix to simulate successful registration.
	 *
	 * @see https://developer.wordpress.org/reference/functions/add_menu_page/
	 *
	 * @param string   $page_title The text to be displayed in the title tags of the page.
	 * @param string   $menu_title The text to be used for the menu.
	 * @param string   $capability The capability required for this menu to be displayed.
	 * @param string   $menu_slug  The slug name to refer to this menu by.
	 * @param callable $callback   The function to be called to output the content.
	 * @param string   $icon_url   The URL to the icon to be used for this menu.
	 * @param int|null $position   The position in the menu order.
	 *
	 * @return string The resulting page's hook_suffix.
	 * @since 1.0.0
	 */
	public function add_menu_page(
		string $page_title,
		string $menu_title,
		string $capability,
		string $menu_slug,
		callable $callback,
		string $icon_url = '',
		int $position = null
	): string {
		if ( $this->is_wp_disabled() ) {
			return $menu_slug;
		}
		return add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $callback, $icon_url, $position );
	}

	/**
	 * Adds a submenu page to a side menu.
	 * FF Logic: Returns the menu_slug to allow tests to verify hook-dependent logic.
	 *
	 * @see https://developer.wordpress.org/reference/functions/add_submenu_page/
	 *
	 * @param string   $parent_slug The slug name for the parent menu.
	 * @param string   $page_title  The text to be displayed in the title tags.
	 * @param string   $menu_title  The text to be used for the menu.
	 * @param string   $capability  The capability required for this menu.
	 * @param string   $menu_slug   The slug name to refer to this menu by.
	 * @param callable $callback    The function to be called to output the content.
	 * @param int|null $position    The position in the menu order.
	 *
	 * @return string|false The resulting page's hook_suffix, or false if the user lacks capability.
	 * @since 1.0.0
	 */
	public function add_submenu_page(
		string $parent_slug,
		string $page_title,
		string $menu_title,
		string $capability,
		string $menu_slug,
		callable $callback,
		int $position = null
	): string|false {
		if ( $this->is_wp_disabled() ) {
			return $menu_slug;
		}
		return add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $callback, $position );
	}
}
