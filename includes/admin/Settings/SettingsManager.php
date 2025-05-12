<?php

namespace WP_OAuth_Debugger\Admin\Settings;

use WP_OAuth_Debugger\Admin\Settings\GeneralSettings;
use WP_OAuth_Debugger\Admin\Settings\SecuritySettings;
use WP_OAuth_Debugger\Admin\Settings\NotificationSettings;
use WP_OAuth_Debugger\Admin\Settings\UpdatesSettings;
use WP_OAuth_Debugger\Admin\Settings\ToolsSettings;

/**
 * Manages all plugin settings
 */
class SettingsManager {
    /**
     * @var GeneralSettings
     */
    private $general_settings;

    /**
     * @var SecuritySettings
     */
    private $security_settings;

    /**
     * @var NotificationSettings
     */
    private $notification_settings;

    /**
     * @var UpdatesSettings
     */
    private $updates_settings;

    /**
     * @var ToolsSettings
     */
    private $tools_settings;

    /**
     * Constructor
     */
    public function __construct() {
        $this->general_settings = new GeneralSettings();
        $this->security_settings = new SecuritySettings();
        $this->notification_settings = new NotificationSettings();
        $this->updates_settings = new UpdatesSettings();
        $this->tools_settings = new ToolsSettings();
    }

    /**
     * Register all plugin settings
     */
    public function register_settings() {
        // Register settings groups
        $this->general_settings->register();
        $this->security_settings->register();
        $this->notification_settings->register();
        $this->updates_settings->register();
        $this->tools_settings->register();
    }

    /**
     * Get settings for a specific tab
     *
     * @param string $tab Tab identifier
     * @return array Settings for the specified tab
     */
    public function get_settings($tab) {
        switch ($tab) {
            case 'general':
                return $this->general_settings->get_settings();
            case 'security':
                return $this->security_settings->get_settings();
            case 'notifications':
                return $this->notification_settings->get_settings();
            case 'updates':
                return $this->updates_settings->get_settings();
            case 'tools':
                return $this->tools_settings->get_settings();
            default:
                return $this->general_settings->get_settings();
        }
    }

    /**
     * Render settings fields for a specific tab
     *
     * @param string $tab Tab identifier
     */
    public function render_settings_fields($tab) {
        switch ($tab) {
            case 'general':
                $this->general_settings->render_fields();
                break;
            case 'security':
                $this->security_settings->render_fields();
                break;
            case 'notifications':
                $this->notification_settings->render_fields();
                break;
            case 'updates':
                $this->updates_settings->render_fields();
                break;
            case 'tools':
                $this->tools_settings->render_fields();
                break;
            default:
                $this->general_settings->render_fields();
                break;
        }
    }

    /**
     * Get the icon for a settings tab
     *
     * @param string $tab Tab identifier
     * @return string Dashicon name
     */
    public function get_tab_icon($tab) {
        $icons = [
            'general' => 'admin-settings',
            'security' => 'shield',
            'notifications' => 'email',
            'updates' => 'update',
            'tools' => 'admin-tools',
        ];

        return $icons[$tab] ?? 'admin-generic';
    }
}
