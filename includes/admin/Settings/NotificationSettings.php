<?php

namespace WP_OAuth_Debugger\Admin\Settings;

/**
 * Notification settings for the plugin
 */
class NotificationSettings extends BaseSettings {
    /**
     * Register settings for this section
     */
    public function register() {
        $this->register_setting('oauth_debug_email_notifications', false);
        $this->register_setting('oauth_debug_notification_email', get_option('admin_email'));
        $this->register_setting('oauth_debug_notification_security_events', true);
        $this->register_setting('oauth_debug_notification_auth_failures', true);
    }

    /**
     * Get settings for this section
     *
     * @return array
     */
    public function get_settings() {
        return array(
            'email_notifications' => $this->get_option('oauth_debug_email_notifications', false),
            'notification_email' => $this->get_option('oauth_debug_notification_email', get_option('admin_email')),
            'notify_security' => $this->get_option('oauth_debug_notification_security_events', true),
            'notify_auth_failures' => $this->get_option('oauth_debug_notification_auth_failures', true)
        );
    }

    /**
     * Render settings fields for this section
     */
    public function render_fields() {
        $settings = $this->get_settings();
?>
        <div class="oauth-debugger-settings-section">
            <h3><?php _e('Email Notifications', 'wp-oauth-debugger'); ?></h3>

            <div class="oauth-debugger-field-row">
                <div class="oauth-debugger-checkbox-item">
                    <input type="checkbox" name="oauth_debug_email_notifications" id="oauth_debug_email_notifications"
                        value="1" <?php checked($settings['email_notifications']); ?> />
                    <label for="oauth_debug_email_notifications">
                        <?php _e('Enable email notifications', 'wp-oauth-debugger'); ?>
                    </label>
                </div>
                <p class="oauth-debugger-field-description">
                    <?php _e('Send email notifications for important OAuth events.', 'wp-oauth-debugger'); ?>
                </p>
            </div>

            <div class="oauth-debugger-field-row">
                <label class="oauth-debugger-field-label" for="oauth_debug_notification_email">
                    <?php _e('Notification Email', 'wp-oauth-debugger'); ?>
                </label>
                <input type="email" name="oauth_debug_notification_email" id="oauth_debug_notification_email"
                    value="<?php echo esc_attr($settings['notification_email']); ?>" />
                <p class="oauth-debugger-field-description">
                    <?php _e('Email address to receive notifications. Default is the admin email.', 'wp-oauth-debugger'); ?>
                </p>
            </div>
        </div>

        <div class="oauth-debugger-settings-section">
            <h3><?php _e('Notification Events', 'wp-oauth-debugger'); ?></h3>

            <div class="oauth-debugger-field-row">
                <div class="oauth-debugger-checkbox-group">
                    <div class="oauth-debugger-checkbox-item">
                        <input type="checkbox" name="oauth_debug_notification_security_events" id="oauth_debug_notification_security_events"
                            value="1" <?php checked($settings['notify_security']); ?> />
                        <label for="oauth_debug_notification_security_events">
                            <?php _e('Security Events', 'wp-oauth-debugger'); ?>
                        </label>
                    </div>
                    <div class="oauth-debugger-checkbox-item">
                        <input type="checkbox" name="oauth_debug_notification_auth_failures" id="oauth_debug_notification_auth_failures"
                            value="1" <?php checked($settings['notify_auth_failures']); ?> />
                        <label for="oauth_debug_notification_auth_failures">
                            <?php _e('Authentication Failures', 'wp-oauth-debugger'); ?>
                        </label>
                    </div>
                </div>
                <p class="oauth-debugger-field-description">
                    <?php _e('Select which events should trigger email notifications.', 'wp-oauth-debugger'); ?>
                </p>
            </div>
        </div>

        <div class="oauth-debugger-settings-section">
            <h3><?php _e('Notification Templates', 'wp-oauth-debugger'); ?></h3>

            <div class="oauth-debugger-field-row">
                <p>
                    <?php _e('Notification templates are currently in development. Custom email templates will be available in a future update.', 'wp-oauth-debugger'); ?>
                </p>
                <div class="oauth-debugger-badge info">
                    <span class="dashicons dashicons-info"></span>
                    <?php _e('Coming Soon', 'wp-oauth-debugger'); ?>
                </div>
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
        return 'notifications';
    }
}
