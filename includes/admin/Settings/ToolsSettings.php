<?php

namespace WP_OAuth_Debugger\Admin\Settings;

/**
 * Tools settings for database management
 */
class ToolsSettings extends BaseSettings {
    /**
     * Register settings for this section
     */
    public function register() {
        // No settings to register as these are action buttons
    }

    /**
     * Get settings for this section
     *
     * @return array
     */
    public function get_settings() {
        return array();
    }

    /**
     * Render settings fields for this section
     */
    public function render_fields() {
?>
        <div class="oauth-debugger-settings-section">
            <h3><?php _e('Database Management', 'wp-oauth-debugger'); ?></h3>
            <p><?php _e('These tools allow you to manage the OAuth Debugger database tables.', 'wp-oauth-debugger'); ?></p>

            <div class="oauth-debugger-tools-actions">
                <div class="oauth-debugger-tool-card">
                    <h4><?php _e('Setup Database', 'wp-oauth-debugger'); ?></h4>
                    <p><?php _e('Create or update the database tables required by the plugin.', 'wp-oauth-debugger'); ?></p>
                    <button type="button" class="button button-primary oauth-debugger-setup-database">
                        <span class="dashicons dashicons-database-add"></span>
                        <?php _e('Setup Database', 'wp-oauth-debugger'); ?>
                    </button>
                </div>

                <div class="oauth-debugger-tool-card">
                    <h4><?php _e('Empty Database', 'wp-oauth-debugger'); ?></h4>
                    <p><?php _e('Clear all data from the database tables without removing the tables.', 'wp-oauth-debugger'); ?></p>
                    <button type="button" class="button button-secondary oauth-debugger-empty-database">
                        <span class="dashicons dashicons-trash"></span>
                        <?php _e('Empty Database', 'wp-oauth-debugger'); ?>
                    </button>
                </div>

                <div class="oauth-debugger-tool-card oauth-debugger-tool-danger">
                    <h4><?php _e('Remove Database Tables', 'wp-oauth-debugger'); ?></h4>
                    <p><?php _e('Remove all database tables created by the plugin. This cannot be undone.', 'wp-oauth-debugger'); ?></p>
                    <button type="button" class="button button-secondary oauth-debugger-remove-database">
                        <span class="dashicons dashicons-warning"></span>
                        <?php _e('Remove Database Tables', 'wp-oauth-debugger'); ?>
                    </button>
                </div>
            </div>
        </div>

        <div class="oauth-debugger-settings-section">
            <h3><?php _e('Plugin Data Reset', 'wp-oauth-debugger'); ?></h3>
            <p><?php _e('Reset all plugin data including settings, logs, and database tables.', 'wp-oauth-debugger'); ?></p>

            <div class="oauth-debugger-tools-actions">
                <div class="oauth-debugger-tool-card oauth-debugger-tool-danger">
                    <h4><?php _e('Reset All Data', 'wp-oauth-debugger'); ?></h4>
                    <p><?php _e('This will reset all plugin data to default values. This action cannot be undone.', 'wp-oauth-debugger'); ?></p>
                    <button type="button" class="button button-secondary oauth-debugger-reset-plugin">
                        <span class="dashicons dashicons-warning"></span>
                        <?php _e('Reset All Plugin Data', 'wp-oauth-debugger'); ?>
                    </button>
                </div>
            </div>
        </div>

        <style>
            .oauth-debugger-tools-actions {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
                gap: 20px;
                margin-top: 20px;
            }

            .oauth-debugger-tool-card {
                background-color: #f8f9fa;
                border: 1px solid #ddd;
                border-radius: 4px;
                padding: 15px;
            }

            .oauth-debugger-tool-danger {
                background-color: #fff8f8;
                border-color: #ffb2b2;
            }

            .oauth-debugger-tool-card h4 {
                margin: 0 0 10px;
                font-size: 16px;
            }

            .oauth-debugger-tool-card p {
                margin: 0 0 15px;
                color: #666;
            }

            .oauth-debugger-tool-card .button {
                display: flex;
                align-items: center;
                gap: 5px;
            }

            .oauth-debugger-tool-danger .button {
                color: #a00;
                border-color: #a00;
            }

            .oauth-debugger-tool-danger .button:hover {
                background-color: #a00;
                color: #fff;
            }

            @media screen and (max-width: 782px) {
                .oauth-debugger-tools-actions {
                    grid-template-columns: 1fr;
                }
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
        return 'tools';
    }
}
