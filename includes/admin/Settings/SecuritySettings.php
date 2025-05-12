<?php

namespace WP_OAuth_Debugger\Admin\Settings;

/**
 * Security settings for the plugin
 */
class SecuritySettings extends BaseSettings {
    /**
     * Register settings for this section
     */
    public function register() {
        $this->register_setting('oauth_debug_security_scan_interval', 24);
        $this->register_setting('oauth_debug_enable_public_panel', false);
        $this->register_setting('oauth_debug_allowed_roles', array('administrator'));
        $this->register_setting('oauth_debug_rate_limit', 60);
        $this->register_setting('oauth_debug_rate_limit_window', 60);
    }

    /**
     * Get settings for this section
     *
     * @return array
     */
    public function get_settings() {
        return array(
            'scan_interval' => $this->get_option('oauth_debug_security_scan_interval', 24),
            'public_panel' => $this->get_option('oauth_debug_enable_public_panel', false),
            'allowed_roles' => $this->get_option('oauth_debug_allowed_roles', array('administrator')),
            'rate_limit' => $this->get_option('oauth_debug_rate_limit', 60),
            'rate_limit_window' => $this->get_option('oauth_debug_rate_limit_window', 60)
        );
    }

    /**
     * Render settings fields for this section
     */
    public function render_fields() {
        $settings = $this->get_settings();
        $roles = get_editable_roles();
?>
        <div class="oauth-debugger-settings-section">
            <h3><?php _e('Security Scan Settings', 'wp-oauth-debugger'); ?></h3>

            <div class="oauth-debugger-field-row">
                <label class="oauth-debugger-field-label" for="oauth_debug_security_scan_interval">
                    <?php _e('Security Scan Interval (Hours)', 'wp-oauth-debugger'); ?>
                </label>
                <input type="number" name="oauth_debug_security_scan_interval" id="oauth_debug_security_scan_interval"
                    value="<?php echo esc_attr($settings['scan_interval']); ?>" min="1" max="168" step="1" />
                <p class="oauth-debugger-field-description">
                    <?php _e('How often to run automated security scans. Recommended: 24 hours.', 'wp-oauth-debugger'); ?>
                </p>
            </div>
        </div>

        <div class="oauth-debugger-settings-section">
            <h3><?php _e('Access Control', 'wp-oauth-debugger'); ?></h3>

            <div class="oauth-debugger-field-row">
                <div class="oauth-debugger-checkbox-item">
                    <input type="checkbox" name="oauth_debug_enable_public_panel" id="oauth_debug_enable_public_panel"
                        value="1" <?php checked($settings['public_panel']); ?> />
                    <label for="oauth_debug_enable_public_panel">
                        <?php _e('Enable public debugging panel', 'wp-oauth-debugger'); ?>
                    </label>
                </div>
                <p class="oauth-debugger-field-description">
                    <?php _e('WARNING: Only enable during development. This will allow accessing debug information without authentication.', 'wp-oauth-debugger'); ?>
                </p>
            </div>

            <div class="oauth-debugger-field-row">
                <label class="oauth-debugger-field-label">
                    <?php _e('Allowed User Roles', 'wp-oauth-debugger'); ?>
                </label>
                <div class="oauth-debugger-checkbox-group">
                    <?php foreach ($roles as $role_key => $role) : ?>
                        <div class="oauth-debugger-checkbox-item">
                            <input type="checkbox" name="oauth_debug_allowed_roles[]"
                                id="role_<?php echo esc_attr($role_key); ?>"
                                value="<?php echo esc_attr($role_key); ?>"
                                <?php checked(in_array($role_key, (array)$settings['allowed_roles'])); ?>
                                <?php disabled($role_key === 'administrator'); ?> />
                            <label for="role_<?php echo esc_attr($role_key); ?>">
                                <?php echo esc_html($role['name']); ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <p class="oauth-debugger-field-description">
                    <?php _e('Select which user roles can access the OAuth Debugger. Administrators always have access.', 'wp-oauth-debugger'); ?>
                </p>
            </div>
        </div>

        <div class="oauth-debugger-settings-section">
            <h3><?php _e('Rate Limiting', 'wp-oauth-debugger'); ?></h3>

            <div class="oauth-debugger-field-row">
                <label class="oauth-debugger-field-label" for="oauth_debug_rate_limit">
                    <?php _e('Rate Limit (Requests)', 'wp-oauth-debugger'); ?>
                </label>
                <input type="number" name="oauth_debug_rate_limit" id="oauth_debug_rate_limit"
                    value="<?php echo esc_attr($settings['rate_limit']); ?>" min="1" max="1000" step="1" />
                <p class="oauth-debugger-field-description">
                    <?php _e('Maximum number of requests allowed within the time window.', 'wp-oauth-debugger'); ?>
                </p>
            </div>

            <div class="oauth-debugger-field-row">
                <label class="oauth-debugger-field-label" for="oauth_debug_rate_limit_window">
                    <?php _e('Rate Limit Window (Seconds)', 'wp-oauth-debugger'); ?>
                </label>
                <input type="number" name="oauth_debug_rate_limit_window" id="oauth_debug_rate_limit_window"
                    value="<?php echo esc_attr($settings['rate_limit_window']); ?>" min="1" max="3600" step="1" />
                <p class="oauth-debugger-field-description">
                    <?php _e('Time window for rate limiting in seconds. Default: 60 seconds (1 minute).', 'wp-oauth-debugger'); ?>
                </p>
            </div>
        </div>
<?php
    }

    /**
     * Get the section ID
     *
     * @return string
     */
    protected function get_section_id() {
        return 'security';
    }
}
