<?php

namespace WP_OAuth_Debugger\Admin\Pages;

use WP_OAuth_Debugger\Admin\Settings\SettingsManager;

/**
 * Settings page with tabbed interface
 */
class SettingsPage extends BasePage {
    /**
     * @var SettingsManager
     */
    private $settings_manager;

    /**
     * @var array
     */
    private $tabs;

    /**
     * Constructor
     *
     * @param SettingsManager $settings_manager
     */
    public function __construct(SettingsManager $settings_manager) {
        $this->settings_manager = $settings_manager;
        $this->tabs = array(
            'general' => __('General', 'wp-oauth-debugger'),
            'security' => __('Security', 'wp-oauth-debugger'),
            'notifications' => __('Notifications', 'wp-oauth-debugger'),
            'updates' => __('Updates', 'wp-oauth-debugger'),
            'tools' => __('Tools', 'wp-oauth-debugger'),
        );
    }

    /**
     * Render the settings page
     */
    public function render() {
        if (!$this->check_permissions()) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'wp-oauth-debugger'));
        }

        // Get current tab
        $current_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'general';
        if (!array_key_exists($current_tab, $this->tabs)) {
            $current_tab = 'general';
        }

        $this->render_header();
        $this->render_tabs($current_tab);
        $this->render_content($current_tab);
        $this->render_footer();
    }

    /**
     * Get the page title
     *
     * @return string
     */
    protected function get_page_title() {
        return __('OAuth Debugger Settings', 'wp-oauth-debugger');
    }

    /**
     * Get the page icon
     *
     * @return string
     */
    protected function get_page_icon() {
        return 'admin-settings';
    }

    /**
     * Render header actions
     */
    protected function render_header_actions() {
?>
        <a href="<?php echo admin_url('admin.php?page=oauth-debugger'); ?>" class="button button-secondary">
            <span class="dashicons dashicons-dashboard"></span>
            <?php _e('Dashboard', 'wp-oauth-debugger'); ?>
        </a>
    <?php
    }

    /**
     * Render the settings tabs
     *
     * @param string $current_tab The current active tab
     */
    private function render_tabs($current_tab) {
    ?>
        <div class="oauth-debugger-tabs-wrapper">
            <nav class="oauth-debugger-tabs-nav">
                <ul>
                    <?php foreach ($this->tabs as $tab_id => $tab_name) : ?>
                        <li class="<?php echo $current_tab === $tab_id ? 'active' : ''; ?>">
                            <a href="<?php echo admin_url('admin.php?page=oauth-debugger-settings&tab=' . $tab_id); ?>">
                                <span class="dashicons dashicons-<?php echo $this->settings_manager->get_tab_icon($tab_id); ?>"></span>
                                <?php echo esc_html($tab_name); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        <?php
    }

    /**
     * Render the settings content for the active tab
     *
     * @param string $current_tab The current active tab
     */
    private function render_content($current_tab) {
        ?>
            <div class="oauth-debugger-tabs-content">
                <div class="oauth-debugger-card">
                    <div class="oauth-debugger-card-header">
                        <h2>
                            <span class="dashicons dashicons-<?php echo $this->settings_manager->get_tab_icon($current_tab); ?>"></span>
                            <?php echo esc_html($this->tabs[$current_tab] ?? ''); ?> <?php _e('Settings', 'wp-oauth-debugger'); ?>
                        </h2>
                    </div>
                    <div class="oauth-debugger-card-body">
                        <form method="post" action="options.php" class="oauth-debugger-settings-form">
                            <?php
                            settings_fields('oauth_debugger_' . $current_tab . '_settings');
                            $this->settings_manager->render_settings_fields($current_tab);
                            submit_button(__('Save Settings', 'wp-oauth-debugger'), 'primary', 'submit', true, array('id' => 'oauth-debugger-save-settings'));
                            ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <style>
            .oauth-debugger-tabs-wrapper {
                display: flex;
                flex-direction: column;
                margin: 20px 0;
            }

            .oauth-debugger-tabs-nav {
                margin-bottom: 20px;
            }

            .oauth-debugger-tabs-nav ul {
                display: flex;
                margin: 0;
                padding: 0;
                list-style: none;
                border-bottom: 1px solid #ddd;
            }

            .oauth-debugger-tabs-nav li {
                margin: 0;
                padding: 0;
            }

            .oauth-debugger-tabs-nav li a {
                display: flex;
                align-items: center;
                gap: 5px;
                padding: 10px 15px;
                color: #666;
                text-decoration: none;
                border: 1px solid transparent;
                border-bottom: none;
                background: transparent;
                transition: all 0.2s ease;
            }

            .oauth-debugger-tabs-nav li a:hover {
                color: #0073aa;
                background: #f8f9fa;
            }

            .oauth-debugger-tabs-nav li.active a {
                color: #0073aa;
                background: #fff;
                border-color: #ddd;
                border-bottom-color: #fff;
                font-weight: 500;
                margin-bottom: -1px;
            }

            .oauth-debugger-settings-form {
                max-width: 100%;
            }

            .oauth-debugger-settings-section {
                margin-bottom: 30px;
                padding-bottom: 20px;
                border-bottom: 1px solid #eee;
            }

            .oauth-debugger-settings-section:last-child {
                border-bottom: none;
                margin-bottom: 0;
                padding-bottom: 0;
            }

            .oauth-debugger-settings-section h3 {
                margin-top: 0;
                margin-bottom: 15px;
                padding-bottom: 8px;
                font-size: 1.2em;
                border-bottom: 1px solid #eee;
            }

            .oauth-debugger-field-row {
                margin-bottom: 20px;
                display: flex;
                flex-direction: column;
            }

            .oauth-debugger-field-row:last-child {
                margin-bottom: 0;
            }

            .oauth-debugger-field-label {
                font-weight: 500;
                margin-bottom: 5px;
            }

            .oauth-debugger-field-description {
                margin-top: 5px;
                color: #666;
                font-size: 0.9em;
                font-style: italic;
            }

            .oauth-debugger-checkbox-group {
                display: flex;
                gap: 20px;
                flex-wrap: wrap;
            }

            .oauth-debugger-checkbox-item {
                display: flex;
                align-items: center;
                gap: 5px;
                margin-bottom: 5px;
            }

            /* Form controls */
            .oauth-debugger-settings-form input[type="text"],
            .oauth-debugger-settings-form input[type="email"],
            .oauth-debugger-settings-form input[type="number"],
            .oauth-debugger-settings-form select {
                min-width: 300px;
            }

            .oauth-debugger-card .submit {
                padding: 15px 0 0 0;
                margin-top: 20px;
                border-top: 1px solid #eee;
            }

            .oauth-debugger-badge {
                display: inline-flex;
                align-items: center;
                gap: 5px;
                padding: 3px 8px;
                border-radius: 3px;
                font-size: 12px;
                font-weight: 500;
                text-transform: uppercase;
                background-color: #f0f0f1;
                color: #50575e;
            }

            .oauth-debugger-badge.info {
                background-color: #e5f5fa;
                color: #0a84a8;
            }

            @media screen and (max-width: 782px) {
                .oauth-debugger-tabs-nav ul {
                    flex-direction: column;
                    border-bottom: none;
                }

                .oauth-debugger-tabs-nav li.active a {
                    border-bottom-color: #ddd;
                    margin-bottom: 0;
                }

                .oauth-debugger-settings-form input[type="text"],
                .oauth-debugger-settings-form input[type="email"],
                .oauth-debugger-settings-form input[type="number"],
                .oauth-debugger-settings-form select {
                    width: 100%;
                    min-width: auto;
                }
            }
        </style>
<?php
    }
}
