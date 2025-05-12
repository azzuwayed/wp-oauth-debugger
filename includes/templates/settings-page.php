<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap oauth-debugger">
    <div class="oauth-debugger-header">
        <h1>
            <span class="dashicons dashicons-admin-settings"></span>
            <?php _e('OAuth Debugger Settings', 'wp-oauth-debugger'); ?>
        </h1>
        <div class="oauth-debugger-header-actions">
            <a href="<?php echo admin_url('admin.php?page=oauth-debugger'); ?>" class="button button-secondary">
                <span class="dashicons dashicons-dashboard"></span>
                <?php _e('Dashboard', 'wp-oauth-debugger'); ?>
            </a>
        </div>
    </div>

    <div class="oauth-debugger-tabs-wrapper">
        <nav class="oauth-debugger-tabs-nav">
            <ul>
                <?php foreach ($tabs as $tab_id => $tab_name) : ?>
                    <li class="<?php echo $current_tab === $tab_id ? 'active' : ''; ?>">
                        <a href="<?php echo admin_url('admin.php?page=oauth-debugger-settings&tab=' . $tab_id); ?>">
                            <span class="dashicons dashicons-<?php echo $this->get_tab_icon($tab_id); ?>"></span>
                            <?php echo esc_html($tab_name); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <div class="oauth-debugger-tabs-content">
            <div class="oauth-debugger-card">
                <div class="oauth-debugger-card-header">
                    <h2>
                        <span class="dashicons dashicons-<?php echo $this->get_tab_icon($current_tab); ?>"></span>
                        <?php echo esc_html($tabs[$current_tab] ?? ''); ?> <?php _e('Settings', 'wp-oauth-debugger'); ?>
                    </h2>
                </div>
                <div class="oauth-debugger-card-body">
                    <form method="post" action="options.php" class="oauth-debugger-settings-form">
                        <?php
                        switch ($current_tab) {
                            case 'general':
                                settings_fields('oauth_debugger_general_settings');
                                $this->render_general_settings_fields();
                                break;
                            case 'security':
                                settings_fields('oauth_debugger_security_settings');
                                $this->render_security_settings_fields();
                                break;
                            case 'notifications':
                                settings_fields('oauth_debugger_notification_settings');
                                $this->render_notification_settings_fields();
                                break;
                            case 'updates':
                                settings_fields('oauth_debugger_updates_settings');
                                $this->render_updates_settings_fields();
                                break;
                            default:
                                settings_fields('oauth_debugger_general_settings');
                                $this->render_general_settings_fields();
                                break;
                        }
                        ?>
                        <?php submit_button(__('Save Settings', 'wp-oauth-debugger'), 'primary', 'submit', true, array('id' => 'oauth-debugger-save-settings')); ?>
                    </form>
                </div>
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
