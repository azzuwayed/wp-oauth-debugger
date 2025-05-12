<?php

namespace WP_OAuth_Debugger\Admin\Settings;

use WP_OAuth_Debugger\Admin\Admin;

/**
 * Updates settings for the plugin
 */
class UpdatesSettings extends BaseSettings {
    /**
     * Register settings for this section
     */
    public function register() {
        $this->register_setting('oauth_debug_auto_updates', false);
        $this->register_setting('oauth_debug_beta_updates', false);
        $this->register_setting('oauth_debug_update_check_interval', 12);
        $this->register_setting('oauth_debug_update_dev_mode', false);
        $this->register_setting('oauth_debug_update_version_override', '');
        $this->register_setting('oauth_debug_update_api_debug', false);
        $this->register_setting('oauth_debug_update_last_response', '');
    }

    /**
     * Get settings for this section
     *
     * @return array
     */
    public function get_settings() {
        return array(
            'auto_updates' => $this->get_option('oauth_debug_auto_updates', false),
            'beta_updates' => $this->get_option('oauth_debug_beta_updates', false),
            'update_interval' => $this->get_option('oauth_debug_update_check_interval', 12),
            'dev_mode' => $this->get_option('oauth_debug_update_dev_mode', false),
            'version_override' => $this->get_option('oauth_debug_update_version_override', ''),
            'api_debug' => $this->get_option('oauth_debug_update_api_debug', false),
            'last_response' => $this->get_option('oauth_debug_update_last_response', '')
        );
    }

    /**
     * Render settings fields for this section
     */
    public function render_fields() {
        $settings = $this->get_settings();
?>
        <div class="oauth-debugger-settings-section">
            <h3><?php _e('Update Settings', 'wp-oauth-debugger'); ?></h3>

            <div class="oauth-debugger-field-row">
                <div class="oauth-debugger-checkbox-item">
                    <input type="checkbox" name="oauth_debug_auto_updates" id="oauth_debug_auto_updates"
                        value="1" <?php checked($settings['auto_updates']); ?> />
                    <label for="oauth_debug_auto_updates">
                        <?php _e('Enable automatic updates', 'wp-oauth-debugger'); ?>
                    </label>
                </div>
                <p class="oauth-debugger-field-description">
                    <?php _e('Automatically update to the latest stable release when available.', 'wp-oauth-debugger'); ?>
                </p>
            </div>

            <div class="oauth-debugger-field-row">
                <div class="oauth-debugger-checkbox-item">
                    <input type="checkbox" name="oauth_debug_beta_updates" id="oauth_debug_beta_updates"
                        value="1" <?php checked($settings['beta_updates']); ?> />
                    <label for="oauth_debug_beta_updates">
                        <?php _e('Include beta releases', 'wp-oauth-debugger'); ?>
                    </label>
                </div>
                <p class="oauth-debugger-field-description">
                    <?php _e('Receive updates for beta versions (not recommended for production sites).', 'wp-oauth-debugger'); ?>
                </p>
            </div>

            <div class="oauth-debugger-field-row">
                <label class="oauth-debugger-field-label" for="oauth_debug_update_check_interval">
                    <?php _e('Update Check Interval (Hours)', 'wp-oauth-debugger'); ?>
                </label>
                <input type="number" name="oauth_debug_update_check_interval" id="oauth_debug_update_check_interval"
                    value="<?php echo esc_attr($settings['update_interval']); ?>" min="1" max="168" step="1" />
                <p class="oauth-debugger-field-description">
                    <?php _e('How often to check for updates. Default: 12 hours.', 'wp-oauth-debugger'); ?>
                </p>
            </div>
        </div>

        <div class="oauth-debugger-settings-section">
            <h3><?php _e('Current Version', 'wp-oauth-debugger'); ?></h3>

            <div class="oauth-debugger-field-row">
                <p>
                    <strong><?php _e('Installed Version:', 'wp-oauth-debugger'); ?></strong>
                    <?php echo esc_html(WP_OAUTH_DEBUGGER_VERSION); ?>
                </p>
                <p>
                    <a href="https://github.com/<?php echo esc_attr(Admin::GITHUB_USERNAME); ?>/<?php echo esc_attr(Admin::GITHUB_REPO); ?>/releases"
                        target="_blank" class="button button-secondary">
                        <span class="dashicons dashicons-external"></span>
                        <?php _e('View Release Notes', 'wp-oauth-debugger'); ?>
                    </a>
                </p>
            </div>
        </div>

        <div class="oauth-debugger-settings-section">
            <h3><?php _e('Development Tools', 'wp-oauth-debugger'); ?> <span class="oauth-debugger-badge"><?php _e('Dev only', 'wp-oauth-debugger'); ?></span></h3>

            <div class="oauth-debugger-field-row">
                <div class="oauth-debugger-checkbox-item">
                    <input type="checkbox" name="oauth_debug_update_dev_mode" id="oauth_debug_update_dev_mode"
                        value="1" <?php checked($settings['dev_mode']); ?> />
                    <label for="oauth_debug_update_dev_mode">
                        <?php _e('Enable development mode', 'wp-oauth-debugger'); ?>
                    </label>
                </div>
                <p class="oauth-debugger-field-description">
                    <?php _e('Enables additional debugging tools and logging for update system development.', 'wp-oauth-debugger'); ?>
                </p>
            </div>

            <div class="oauth-debugger-field-row" id="oauth-update-dev-tools" style="<?php echo $settings['dev_mode'] ? '' : 'display: none;'; ?>">
                <div class="oauth-debugger-dev-tools">
                    <div class="oauth-debugger-dev-tool-section">
                        <h4><?php _e('Manual Update Check', 'wp-oauth-debugger'); ?></h4>
                        <p><?php _e('Trigger an immediate check for updates, bypassing the cache.', 'wp-oauth-debugger'); ?></p>
                        <button type="button" id="oauth-debugger-check-updates-now" class="button">
                            <span class="dashicons dashicons-update"></span>
                            <?php _e('Check for Updates Now', 'wp-oauth-debugger'); ?>
                        </button>
                        <div id="oauth-debugger-update-check-result" class="oauth-debugger-update-check-result"></div>
                    </div>

                    <div class="oauth-debugger-dev-tool-section">
                        <h4><?php _e('Version Control', 'wp-oauth-debugger'); ?></h4>
                        <p><?php _e('Override the local version number to test update detection.', 'wp-oauth-debugger'); ?></p>
                        <div class="oauth-debugger-field-row">
                            <label class="oauth-debugger-field-label" for="oauth_debug_update_version_override">
                                <?php _e('Version Override', 'wp-oauth-debugger'); ?>
                            </label>
                            <input type="text" name="oauth_debug_update_version_override" id="oauth_debug_update_version_override"
                                value="<?php echo esc_attr($settings['version_override']); ?>" placeholder="e.g. 1.0.0" />
                            <p class="oauth-debugger-field-description">
                                <?php _e('Enter a version number to test update detection (e.g., "1.0.0" to simulate an older version).', 'wp-oauth-debugger'); ?>
                            </p>
                        </div>
                    </div>

                    <div class="oauth-debugger-dev-tool-section">
                        <h4><?php _e('API Debug', 'wp-oauth-debugger'); ?></h4>
                        <div class="oauth-debugger-checkbox-item">
                            <input type="checkbox" name="oauth_debug_update_api_debug" id="oauth_debug_update_api_debug"
                                value="1" <?php checked($settings['api_debug']); ?> />
                            <label for="oauth_debug_update_api_debug">
                                <?php _e('Log API responses', 'wp-oauth-debugger'); ?>
                            </label>
                        </div>
                        <p class="oauth-debugger-field-description">
                            <?php _e('Store and display GitHub API responses for debugging.', 'wp-oauth-debugger'); ?>
                        </p>

                        <?php if (!empty($settings['last_response'])) : ?>
                            <div class="oauth-debugger-api-response">
                                <h5><?php _e('Last API Response', 'wp-oauth-debugger'); ?></h5>
                                <textarea readonly class="oauth-debugger-api-response-data"><?php echo esc_textarea($settings['last_response']); ?></textarea>
                                <p>
                                    <button type="button" id="oauth-debugger-clear-api-response" class="button">
                                        <?php _e('Clear Response Data', 'wp-oauth-debugger'); ?>
                                    </button>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <script>
            jQuery(document).ready(function($) {
                // Toggle development tools visibility
                $('#oauth_debug_update_dev_mode').on('change', function() {
                    if ($(this).is(':checked')) {
                        $('#oauth-update-dev-tools').show();
                    } else {
                        $('#oauth-update-dev-tools').hide();
                    }
                });

                // Manual update check
                $('#oauth-debugger-check-updates-now').on('click', function() {
                    var $button = $(this);
                    var $result = $('#oauth-debugger-update-check-result');

                    $button.prop('disabled', true).addClass('updating-message');
                    $result.html('<p class="oauth-debugger-loading"><?php _e('Checking for updates...', 'wp-oauth-debugger'); ?></p>');

                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'oauth_debugger_manual_update_check',
                            nonce: '<?php echo wp_create_nonce('oauth_debugger_update_check'); ?>',
                            version_override: $('#oauth_debug_update_version_override').val()
                        },
                        success: function(response) {
                            if (response.success) {
                                $result.html('<div class="oauth-debugger-update-success"><p>' + response.data.message + '</p></div>');

                                if (response.data.api_response) {
                                    $('.oauth-debugger-api-response-data').val(response.data.api_response);
                                }

                                if (response.data.update_available) {
                                    $result.append('<div class="oauth-debugger-update-available">' +
                                        '<p><strong><?php _e('Update Available!', 'wp-oauth-debugger'); ?></strong></p>' +
                                        '<p><?php _e('Version:', 'wp-oauth-debugger'); ?> ' + response.data.new_version + '</p>' +
                                        '<p><a href="' + response.data.package_url + '" target="_blank" class="button button-small">' +
                                        '<?php _e('View Package', 'wp-oauth-debugger'); ?></a></p>' +
                                        '</div>');
                                } else {
                                    $result.append('<p><?php _e('No update available.', 'wp-oauth-debugger'); ?></p>');
                                }
                            } else {
                                $result.html('<div class="oauth-debugger-update-error"><p>' + response.data.message + '</p></div>');
                            }
                        },
                        error: function() {
                            $result.html('<div class="oauth-debugger-update-error"><p><?php _e('Error checking for updates.', 'wp-oauth-debugger'); ?></p></div>');
                        },
                        complete: function() {
                            $button.prop('disabled', false).removeClass('updating-message');
                        }
                    });
                });

                // Clear API response
                $('#oauth-debugger-clear-api-response').on('click', function() {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'oauth_debugger_clear_api_response',
                            nonce: '<?php echo wp_create_nonce('oauth_debugger_clear_api_response'); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                $('.oauth-debugger-api-response').fadeOut(300, function() {
                                    $(this).remove();
                                });
                            }
                        }
                    });
                });
            });
        </script>

        <style>
            .oauth-debugger-dev-tools {
                background-color: #f8f9fa;
                border: 1px solid #ddd;
                border-radius: 4px;
                padding: 15px;
                margin-top: 10px;
            }

            .oauth-debugger-dev-tool-section {
                margin-bottom: 20px;
                padding-bottom: 20px;
                border-bottom: 1px solid #eee;
            }

            .oauth-debugger-dev-tool-section:last-child {
                margin-bottom: 0;
                padding-bottom: 0;
                border-bottom: none;
            }

            .oauth-debugger-dev-tool-section h4 {
                margin: 0 0 10px;
                font-size: 14px;
                font-weight: 600;
                color: #1d2327;
            }

            .oauth-debugger-update-check-result {
                margin-top: 15px;
                padding: 10px;
                background-color: #f0f0f1;
                border-radius: 4px;
                display: none;
            }

            .oauth-debugger-update-check-result:not(:empty) {
                display: block;
            }

            .oauth-debugger-loading {
                display: flex;
                align-items: center;
                gap: 8px;
                color: #666;
                font-style: italic;
            }

            .oauth-debugger-loading:before {
                content: '';
                display: inline-block;
                width: 16px;
                height: 16px;
                border: 2px solid #f3f3f3;
                border-top: 2px solid #3498db;
                border-radius: 50%;
                animation: oauth-debugger-spin 1s linear infinite;
            }

            @keyframes oauth-debugger-spin {
                0% {
                    transform: rotate(0deg);
                }

                100% {
                    transform: rotate(360deg);
                }
            }

            .oauth-debugger-update-success {
                color: #2c7b30;
            }

            .oauth-debugger-update-error {
                color: #cc1818;
            }

            .oauth-debugger-update-available {
                background-color: #e5f5fa;
                border-left: 4px solid #00a0d2;
                padding: 10px;
                margin-top: 10px;
            }

            .oauth-debugger-api-response {
                margin-top: 15px;
            }

            .oauth-debugger-api-response h5 {
                margin: 0 0 10px;
                font-size: 13px;
                font-weight: normal;
            }

            .oauth-debugger-api-response-data {
                width: 100%;
                height: 200px;
                font-family: monospace;
                font-size: 12px;
                background-color: #f0f0f1;
                border: 1px solid #ddd;
                padding: 10px;
                resize: vertical;
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
        return 'updates';
    }
}
