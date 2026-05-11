<?php
/**
 * Main Bridge to WordPress core functions.
 *
 * @package    Triskelion\TriskelionToolkit
 * @subpackage Core\Bridge
 * @since      1.0.0
 */

namespace Triskelion\TriskelionToolkit\Core\Bridge;

/**
 * Class WpBridge
 *
 * Acts as a Facade for specialized WordPress wrappers.
 *
 * @package Triskelion\TriskelionToolkit\Core\Bridge
 */
class WpBridge {

	/**
	 * Menu wrapper.
	 *
	 * @var WpMenu
	 */
	public WpMenu $menu;

	/**
	 * Settings wrapper.
	 *
	 * @var WpSettings
	 */
	public WpSettings $settings;

	/**
	 * Security wrapper.
	 *
	 * @var WpSecurity
	 */
	public WpSecurity $security;

	/**
	 * Events wrapper (hooks, filters, etc.).
	 *
	 * @var WpEvents
	 */
	public WpEvents $events;

	/**
	 * WpBridge constructor.
	 */
	public function __construct() {
		$this->menu     = new WpMenu();
		$this->settings = new WpSettings();
		$this->security = new WpSecurity();
		$this->events   = new WpEvents();
	}

	/**
	 * Sets the Menu wrapper instance.
	 *
	 * Primarily used for injecting mocks during testing.
	 *
	 * @param WpMenu $menu Menu wrapper instance.
	 * @return self
	 */
	public function set_menu( WpMenu $menu ): self {
		$this->menu = $menu;
		return $this;
	}

	/**
	 * Sets the Settings wrapper instance.
	 *
	 * @param WpSettings $settings Settings wrapper instance.
	 * @return self
	 */
	public function set_settings( WpSettings $settings ): self {
		$this->settings = $settings;
		return $this;
	}

	/**
	 * Sets the Security wrapper instance.
	 *
	 * @param WpSecurity $security Security wrapper instance.
	 * @return self
	 */
	public function set_security( WpSecurity $security ): self {
		$this->security = $security;
		return $this;
	}

	/**
	 * Sets the Hooks wrapper instance.
	 *
	 * @param WpEvents $events Hooks wrapper instance.
	 *
	 * @return self
	 */
	public function set_events( WpEvents $events ): self {
		$this->events = $events;
		return $this;
	}
}
