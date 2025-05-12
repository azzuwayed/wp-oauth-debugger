<?php

namespace WP_OAuth_Debugger\Admin\Settings;

/**
 * Base class for all settings pages
 */
abstract class BaseSettings {
    /**
     * Register settings for this section
     */
    abstract public function register();

    /**
     * Get settings for this section
     *
     * @return array
     */
    abstract public function get_settings();

    /**
     * Render settings fields for this section
     */
    abstract public function render_fields();

    /**
     * Register a single setting
     *
     * @param string $option_name The name of the option to register
     * @param mixed  $default     The default value
     */
    protected function register_setting($option_name, $default = '') {
        register_setting($this->get_option_group(), $option_name);
        add_option($option_name, $default);
    }

    /**
     * Get the option group name for this settings section
     *
     * @return string
     */
    protected function get_option_group() {
        return 'oauth_debugger_' . $this->get_section_id() . '_settings';
    }

    /**
     * Get the section ID
     *
     * @return string
     */
    abstract protected function get_section_id();

    /**
     * Get a setting value
     *
     * @param string $option_name The name of the option
     * @param mixed  $default     The default value if option doesn't exist
     * @return mixed
     */
    protected function get_option($option_name, $default = '') {
        return get_option($option_name, $default);
    }

    /**
     * Update a setting value
     *
     * @param string $option_name  The name of the option
     * @param mixed  $option_value The value to set
     * @return bool
     */
    protected function update_option($option_name, $option_value) {
        return update_option($option_name, $option_value);
    }

    /**
     * Delete a setting
     *
     * @param string $option_name The name of the option
     * @return bool
     */
    protected function delete_option($option_name) {
        return delete_option($option_name);
    }
}
