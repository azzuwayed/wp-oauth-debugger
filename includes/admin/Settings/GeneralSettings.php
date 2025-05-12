<?php

namespace WP_OAuth_Debugger\Admin\Settings;

/**
 * General settings for the plugin
 */
class GeneralSettings extends BaseSettings {
    /**
     * Register settings for this section
     */
    public function register() {
        $this->register_setting('oauth_debug_log_level', 'info');
        $this->register_setting('oauth_debug_log_retention', 7);
        $this->register_setting('oauth_debug_auto_cleanup', true);
        $this->register_setting('oauth_debug_clear_logs_on_deactivate', false);
        $this->register_setting('oauth_debug_remove_all_on_uninstall', false);
    }

    /**
     * Get settings for this section
     *
     * @return array
     */
    public function get_settings() {
        return array(
            'log_level' => $this->get_option('oauth_debug_log_level', 'info'),
            'log_retention' => $this->get_option('oauth_debug_log_retention', 7),
            'auto_cleanup' => $this->get_option('oauth_debug_auto_cleanup', true),
            'clear_on_deactivate' => $this->get_option('oauth_debug_clear_logs_on_deactivate', false),
            'remove_all_on_uninstall' => $this->get_option('oauth_debug_remove_all_on_uninstall', false)
        );
    }

    /**
     * Render settings fields for this section
     */
    public function render_fields() {
        $settings = $this->get_settings();
?>
        <div class="oauth-debugger-settings-section">
            <h3><?php _e('Logging Configuration', 'wp-oauth-debugger'); ?></h3>

            <div class="oauth-debugger-field-row">
                <label class="oauth-debugger-field-label" for="oauth_debug_log_level">
                    <?php _e('Log Level', 'wp-oauth-debugger'); ?>
                </label>
                <select name="oauth_debug_log_level" id="oauth_debug_log_level">
                    <option value="debug" <?php selected($settings['log_level'], 'debug'); ?>>
                        <?php _e('Debug (Most Verbose)', 'wp-oauth-debugger'); ?>
                    </option>
                    <option value="info" <?php selected($settings['log_level'], 'info'); ?>>
                        <?php _e('Info (Recommended)', 'wp-oauth-debugger'); ?>
                    </option>
                    <option value="warning" <?php selected($settings['log_level'], 'warning'); ?>>
                        <?php _e('Warning', 'wp-oauth-debugger'); ?>
                    </option>
                    <option value="error" <?php selected($settings['log_level'], 'error'); ?>>
                        <?php _e('Error (Least Verbose)', 'wp-oauth-debugger'); ?>
                    </option>
                </select>
                <p class="oauth-debugger-field-description">
                    <?php _e('Select how detailed the logs should be. More verbose levels will generate more logs.', 'wp-oauth-debugger'); ?>
                </p>
            </div>

            <div class="oauth-debugger-field-row">
                <label class="oauth-debugger-field-label" for="oauth_debug_log_retention">
                    <?php _e('Log Retention Period (Days)', 'wp-oauth-debugger'); ?>
                </label>
                <input type="number" name="oauth_debug_log_retention" id="oauth_debug_log_retention"
                    value="<?php echo esc_attr($settings['log_retention']); ?>" min="1" max="90" step="1" />
                <p class="oauth-debugger-field-description">
                    <?php _e('Number of days to keep logs before automatic deletion. Recommended: 7-30 days.', 'wp-oauth-debugger'); ?>
                </p>
            </div>

            <div class="oauth-debugger-field-row">
                <div class="oauth-debugger-checkbox-item">
                    <input type="checkbox" name="oauth_debug_auto_cleanup" id="oauth_debug_auto_cleanup"
                        value="1" <?php checked($settings['auto_cleanup']); ?> />
                    <label for="oauth_debug_auto_cleanup">
                        <?php _e('Enable automatic log cleanup', 'wp-oauth-debugger'); ?>
                    </label>
                </div>
                <p class="oauth-debugger-field-description">
                    <?php _e('Automatically delete logs older than the retention period.', 'wp-oauth-debugger'); ?>
                </p>
            </div>

            <div class="oauth-debugger-field-row">
                <div class="oauth-debugger-checkbox-item">
                    <input type="checkbox" name="oauth_debug_clear_logs_on_deactivate" id="oauth_debug_clear_logs_on_deactivate"
                        value="1" <?php checked($settings['clear_on_deactivate']); ?> />
                    <label for="oauth_debug_clear_logs_on_deactivate">
                        <?php _e('Clear logs on plugin deactivation', 'wp-oauth-debugger'); ?>
                    </label>
                </div>
                <p class="oauth-debugger-field-description">
                    <?php _e('Remove all logs when the plugin is deactivated.', 'wp-oauth-debugger'); ?>
                </p>
            </div>

            <div class="oauth-debugger-field-row">
                <div class="oauth-debugger-checkbox-item oauth-debugger-danger-option">
                    <input type="checkbox" name="oauth_debug_remove_all_on_uninstall" id="oauth_debug_remove_all_on_uninstall"
                        value="1" <?php checked($settings['remove_all_on_uninstall']); ?> />
                    <label for="oauth_debug_remove_all_on_uninstall">
                        <?php _e('Remove all data on plugin uninstall', 'wp-oauth-debugger'); ?>
                    </label>
                </div>
                <p class="oauth-debugger-field-description oauth-debugger-danger-description">
                    <?php _e('Remove all plugin data including settings, logs, and database tables when the plugin is uninstalled. This cannot be undone.', 'wp-oauth-debugger'); ?>
                </p>
            </div>
        </div>

        <div class="oauth-debugger-settings-section">
            <h3><?php _e('Debug Console', 'wp-oauth-debugger'); ?></h3>

            <div class="oauth-debugger-field-row">
                <p>
                    <?php _e('The debug console provides real-time insight into OAuth flows and requests.', 'wp-oauth-debugger'); ?>
                </p>
                <p>
                    <a href="<?php echo admin_url('admin.php?page=oauth-debugger-monitor'); ?>" class="button button-secondary">
                        <span class="dashicons dashicons-visibility"></span>
                        <?php _e('Open Live Monitor', 'wp-oauth-debugger'); ?>
                    </a>
                </p>
            </div>
        </div>

        <style>
            .oauth-debugger-danger-option label {
                color: #a00;
                font-weight: 500;
            }

            .oauth-debugger-danger-description {
                color: #a00 !important;
            }
        </style>
<?php
    }

    /**
     * Get the section ID
     *
     * @return string
     */
    protected function get_section_id() {
        return 'general';
    }
}
