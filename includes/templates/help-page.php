<?php

/**
 * Help and Documentation Page Template
 *
 * @package WP_OAuth_Debugger
 * @subpackage Templates
 */

if (!defined('ABSPATH')) {
    exit;
}

$current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'overview';
$tabs = array(
    'overview' => __('Overview', 'wp-oauth-debugger'),
    'features' => __('Features', 'wp-oauth-debugger'),
    'configuration' => __('Configuration', 'wp-oauth-debugger'),
    'examples' => __('Examples', 'wp-oauth-debugger'),
    'troubleshooting' => __('Troubleshooting', 'wp-oauth-debugger')
);
?>

<div class="wrap oauth-debugger-help">
    <h1><?php _e('OAuth Debugger Help & Documentation', 'wp-oauth-debugger'); ?></h1>

    <nav class="nav-tab-wrapper">
        <?php foreach ($tabs as $tab_id => $tab_name) : ?>
            <a href="?page=oauth-debugger-help&tab=<?php echo esc_attr($tab_id); ?>"
                class="nav-tab <?php echo $current_tab === $tab_id ? 'nav-tab-active' : ''; ?>">
                <?php echo esc_html($tab_name); ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="oauth-debugger-help-content">
        <?php
        switch ($current_tab) {
            case 'overview':
                include plugin_dir_path(__FILE__) . 'help/overview.php';
                break;
            case 'features':
                include plugin_dir_path(__FILE__) . 'help/features.php';
                break;
            case 'configuration':
                include plugin_dir_path(__FILE__) . 'help/configuration.php';
                break;
            case 'examples':
                include plugin_dir_path(__FILE__) . 'help/examples.php';
                break;
            case 'troubleshooting':
                include plugin_dir_path(__FILE__) . 'help/troubleshooting.php';
                break;
        }
        ?>
    </div>
</div>

<style>
    .oauth-debugger-help {
        max-width: 1200px;
        margin: 20px;
    }

    .oauth-debugger-help-content {
        background: #fff;
        padding: 20px;
        border: 1px solid #ccd0d4;
        border-top: none;
    }

    .oauth-debugger-help-section {
        margin-bottom: 30px;
    }

    .oauth-debugger-help-section h2 {
        margin-top: 0;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }

    .oauth-debugger-help-section h3 {
        margin: 20px 0 10px;
        color: #1d2327;
    }

    .oauth-debugger-help-section code {
        background: #f0f0f1;
        padding: 3px 5px;
        border-radius: 3px;
    }

    .oauth-debugger-help-section pre {
        background: #f6f7f7;
        padding: 15px;
        border: 1px solid #dcdcde;
        border-radius: 4px;
        overflow-x: auto;
    }

    .oauth-debugger-help-section .note {
        background: #f0f6fc;
        border-left: 4px solid #72aee6;
        padding: 12px;
        margin: 15px 0;
    }

    .oauth-debugger-help-section .warning {
        background: #fcf9e8;
        border-left: 4px solid #dba617;
        padding: 12px;
        margin: 15px 0;
    }

    .oauth-debugger-help-section .example-box {
        background: #f6f7f7;
        border: 1px solid #dcdcde;
        border-radius: 4px;
        padding: 15px;
        margin: 15px 0;
    }

    .oauth-debugger-help-section .example-box h4 {
        margin-top: 0;
        color: #1d2327;
    }

    .oauth-debugger-help-section table {
        border-collapse: collapse;
        width: 100%;
        margin: 15px 0;
    }

    .oauth-debugger-help-section th,
    .oauth-debugger-help-section td {
        border: 1px solid #dcdcde;
        padding: 8px 12px;
        text-align: left;
    }

    .oauth-debugger-help-section th {
        background: #f6f7f7;
    }

    .oauth-debugger-help-section .troubleshooting-item {
        margin-bottom: 20px;
        padding-bottom: 20px;
        border-bottom: 1px solid #eee;
    }

    .oauth-debugger-help-section .troubleshooting-item:last-child {
        border-bottom: none;
    }

    .oauth-debugger-help-section .troubleshooting-item h4 {
        margin: 0 0 10px;
        color: #1d2327;
    }

    .oauth-debugger-help-section .troubleshooting-item .solution {
        margin-top: 10px;
        padding: 10px;
        background: #f0f6fc;
        border-left: 4px solid #72aee6;
    }
</style>
