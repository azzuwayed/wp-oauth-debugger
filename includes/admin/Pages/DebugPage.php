<?php

namespace WP_OAuth_Debugger\Admin\Pages;

use WP_OAuth_Debugger\Debug\DebugHelper;

/**
 * Main debug dashboard page
 */
class DebugPage extends BasePage {
    /**
     * @var DebugHelper
     */
    private $debug_helper;

    /**
     * Constructor
     */
    public function __construct() {
        $this->debug_helper = new DebugHelper();
    }

    /**
     * Render the debug page
     */
    public function render() {
        if (!$this->check_permissions()) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'wp-oauth-debugger'));
        }

        $this->render_header();
        $this->render_content();
        $this->render_footer();
    }

    /**
     * Get the page title
     *
     * @return string
     */
    protected function get_page_title() {
        return __('OAuth Debugger', 'wp-oauth-debugger');
    }

    /**
     * Get the page icon
     *
     * @return string
     */
    protected function get_page_icon() {
        return 'search';
    }

    /**
     * Render header actions
     */
    protected function render_header_actions() {
?>
        <a href="<?php echo admin_url('admin.php?page=oauth-debugger-settings'); ?>" class="button button-secondary">
            <span class="dashicons dashicons-admin-settings"></span>
            <?php _e('Settings', 'wp-oauth-debugger'); ?>
        </a>
        <a href="<?php echo admin_url('admin.php?page=oauth-debugger-help'); ?>" class="button button-secondary">
            <span class="dashicons dashicons-editor-help"></span>
            <?php _e('Help', 'wp-oauth-debugger'); ?>
        </a>
    <?php
    }

    /**
     * Render the main content
     */
    private function render_content() {
        $oauth_stats = $this->debug_helper->get_statistics();
        $recent_logs = $this->debug_helper->get_recent_logs(5);
        $active_tokens = $this->debug_helper->get_active_tokens();
        $active_tokens = array_slice($active_tokens, 0, 5);
        $security_status = $this->debug_helper->get_security_status();
    ?>
        <div class="oauth-debugger-dashboard">
            <div class="oauth-debugger-dashboard-column main">
                <?php $this->render_stats_cards($oauth_stats); ?>
                <?php $this->render_recent_logs($recent_logs); ?>
            </div>
            <div class="oauth-debugger-dashboard-column sidebar">
                <?php $this->render_quick_actions(); ?>
                <?php $this->render_active_tokens($active_tokens); ?>
                <?php $this->render_security_status($security_status); ?>
            </div>
        </div>

        <style>
            .oauth-debugger-dashboard {
                display: flex;
                flex-wrap: wrap;
                gap: 20px;
                margin-top: 20px;
            }

            .oauth-debugger-dashboard-column.main {
                flex: 1;
                min-width: 500px;
            }

            .oauth-debugger-dashboard-column.sidebar {
                width: 300px;
            }

            .oauth-debugger-stats-cards {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 15px;
                margin-bottom: 20px;
            }

            .oauth-debugger-stat-card {
                background-color: #fff;
                border: 1px solid #ddd;
                border-radius: 4px;
                padding: 15px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            }

            .oauth-debugger-stat-card .title {
                font-size: 14px;
                color: #666;
                margin: 0;
            }

            .oauth-debugger-stat-card .value {
                font-size: 32px;
                font-weight: 600;
                margin: 10px 0 5px;
                color: #2271b1;
            }

            .oauth-debugger-stat-card .trend {
                font-size: 12px;
                color: #666;
            }

            .oauth-debugger-stat-card .trend.up {
                color: #46b450;
            }

            .oauth-debugger-stat-card .trend.down {
                color: #dc3232;
            }

            .oauth-debugger-card {
                background-color: #fff;
                border: 1px solid #ddd;
                border-radius: 4px;
                margin-bottom: 20px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            }

            .oauth-debugger-card-header {
                padding: 10px 15px;
                border-bottom: 1px solid #ddd;
                background-color: #f8f9fa;
            }

            .oauth-debugger-card-header h2 {
                margin: 0;
                font-size: 14px;
                display: flex;
                align-items: center;
                gap: 5px;
            }

            .oauth-debugger-card-body {
                padding: 15px;
            }

            .oauth-debugger-card-footer {
                padding: 10px 15px;
                border-top: 1px solid #ddd;
                background-color: #f8f9fa;
                text-align: right;
            }

            .oauth-debugger-quick-actions {
                display: grid;
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .oauth-debugger-quick-actions .button {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 5px;
                text-align: center;
            }

            .oauth-debugger-logs-table {
                width: 100%;
                border-collapse: collapse;
            }

            .oauth-debugger-logs-table th,
            .oauth-debugger-logs-table td {
                padding: 8px 10px;
                text-align: left;
                border-bottom: 1px solid #eee;
            }

            .oauth-debugger-logs-table th {
                background-color: #f8f9fa;
                font-weight: 500;
            }

            .oauth-debugger-logs-table tr:last-child td {
                border-bottom: none;
            }

            .oauth-debugger-log-level {
                display: inline-block;
                padding: 2px 6px;
                border-radius: 3px;
                font-size: 11px;
                text-transform: uppercase;
                font-weight: 500;
            }

            .oauth-debugger-log-level.info {
                background-color: #e5f5fa;
                color: #0a84a8;
            }

            .oauth-debugger-log-level.warning {
                background-color: #fcf9e8;
                color: #a27d35;
            }

            .oauth-debugger-log-level.error {
                background-color: #fbeaea;
                color: #c02b2b;
            }

            .oauth-debugger-tokens-list {
                list-style: none;
                margin: 0;
                padding: 0;
            }

            .oauth-debugger-tokens-list li {
                padding: 10px 0;
                border-bottom: 1px solid #eee;
            }

            .oauth-debugger-tokens-list li:last-child {
                border-bottom: none;
            }

            .oauth-debugger-token-info {
                margin-bottom: 5px;
                font-weight: 500;
            }

            .oauth-debugger-token-meta {
                display: flex;
                justify-content: space-between;
                gap: 10px;
                font-size: 12px;
                color: #666;
            }

            .oauth-debugger-token-actions {
                margin-top: 5px;
            }

            .oauth-debugger-token-actions .button {
                font-size: 12px;
                min-height: 26px;
                padding: 0 8px;
                line-height: 24px;
            }

            .oauth-debugger-security-status {
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 10px;
                border-radius: 4px;
                margin-bottom: 10px;
            }

            .oauth-debugger-security-status.secure {
                background-color: #edfaef;
                color: #2a803b;
            }

            .oauth-debugger-security-status.warning {
                background-color: #fcf9e8;
                color: #a27d35;
            }

            .oauth-debugger-security-status.danger {
                background-color: #fbeaea;
                color: #c02b2b;
            }

            .oauth-debugger-security-issues {
                list-style: none;
                margin: 0;
                padding: 0;
            }

            .oauth-debugger-security-issues li {
                padding: 8px 0;
                border-bottom: 1px solid #eee;
                display: flex;
                align-items: flex-start;
                gap: 8px;
            }

            .oauth-debugger-security-issues li:last-child {
                border-bottom: none;
            }

            .oauth-debugger-security-issue-icon {
                flex-shrink: 0;
                margin-top: 3px;
            }

            @media screen and (max-width: 782px) {
                .oauth-debugger-dashboard-column.main {
                    min-width: 100%;
                }

                .oauth-debugger-dashboard-column.sidebar {
                    width: 100%;
                }
            }
        </style>
    <?php
    }

    /**
     * Render statistics cards
     *
     * @param array $stats Statistics data
     */
    private function render_stats_cards($stats) {
    ?>
        <div class="oauth-debugger-stats-cards">
            <div class="oauth-debugger-stat-card">
                <p class="title"><?php _e('Total Requests', 'wp-oauth-debugger'); ?></p>
                <p class="value"><?php echo esc_html(number_format_i18n($stats['total_requests'] ?? 0)); ?></p>
                <p class="trend <?php echo ($stats['requests_trend'] ?? 0) >= 0 ? 'up' : 'down'; ?>">
                    <?php echo ($stats['requests_trend'] ?? 0) >= 0 ? '+' : ''; ?><?php echo esc_html($stats['requests_trend'] ?? '0'); ?>%
                    <?php _e('vs previous period', 'wp-oauth-debugger'); ?>
                </p>
            </div>
            <div class="oauth-debugger-stat-card">
                <p class="title"><?php _e('Active Tokens', 'wp-oauth-debugger'); ?></p>
                <p class="value"><?php echo esc_html(number_format_i18n($stats['active_tokens'] ?? 0)); ?></p>
                <p class="trend">
                    <?php _e('From', 'wp-oauth-debugger'); ?> <?php echo esc_html($stats['unique_clients'] ?? '0'); ?>
                    <?php _e('client(s)', 'wp-oauth-debugger'); ?>
                </p>
            </div>
            <div class="oauth-debugger-stat-card">
                <p class="title"><?php _e('Auth Failures', 'wp-oauth-debugger'); ?></p>
                <p class="value"><?php echo esc_html(number_format_i18n($stats['auth_failures'] ?? 0)); ?></p>
                <p class="trend <?php echo ($stats['failures_trend'] ?? 0) >= 0 ? 'up' : 'down'; ?>">
                    <?php echo ($stats['failures_trend'] ?? 0) >= 0 ? '+' : ''; ?><?php echo esc_html($stats['failures_trend'] ?? '0'); ?>%
                    <?php _e('vs previous period', 'wp-oauth-debugger'); ?>
                </p>
            </div>
            <div class="oauth-debugger-stat-card">
                <p class="title"><?php _e('Success Rate', 'wp-oauth-debugger'); ?></p>
                <p class="value"><?php echo esc_html($stats['success_rate'] ?? '0'); ?>%</p>
                <p class="trend">
                    <?php _e('Last 30 days', 'wp-oauth-debugger'); ?>
                </p>
            </div>
        </div>
    <?php
    }

    /**
     * Render recent logs table
     *
     * @param array $logs Recent logs data
     */
    private function render_recent_logs($logs) {
        ob_start();
    ?>
        <?php if (empty($logs)) : ?>
            <p><?php _e('No recent logs found.', 'wp-oauth-debugger'); ?></p>
        <?php else : ?>
            <table class="oauth-debugger-logs-table">
                <thead>
                    <tr>
                        <th><?php _e('Time', 'wp-oauth-debugger'); ?></th>
                        <th><?php _e('Level', 'wp-oauth-debugger'); ?></th>
                        <th><?php _e('Message', 'wp-oauth-debugger'); ?></th>
                        <th><?php _e('Client', 'wp-oauth-debugger'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log) : ?>
                        <tr>
                            <td><?php echo esc_html(date_i18n('Y-m-d H:i:s', strtotime($log['timestamp']))); ?></td>
                            <td>
                                <span class="oauth-debugger-log-level <?php echo esc_attr(strtolower($log['level'])); ?>">
                                    <?php echo esc_html($log['level']); ?>
                                </span>
                            </td>
                            <td><?php echo esc_html($log['message']); ?></td>
                            <td><?php echo esc_html($log['client_id'] ?? '-'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    <?php
        $content = ob_get_clean();

        $footer = '<a href="' . admin_url('admin.php?page=oauth-debugger-monitor') . '" class="button button-secondary">' .
            '<span class="dashicons dashicons-visibility"></span> ' .
            __('View All Logs', 'wp-oauth-debugger') . '</a>';

        $this->render_card(
            __('Recent OAuth Activity', 'wp-oauth-debugger'),
            $content,
            array(
                'icon' => 'chart-area',
                'footer' => $footer
            )
        );
    }

    /**
     * Render quick actions section
     */
    private function render_quick_actions() {
        ob_start();
    ?>
        <div class="oauth-debugger-quick-actions">
            <a href="<?php echo admin_url('admin.php?page=oauth-debugger-monitor'); ?>" class="button button-primary">
                <span class="dashicons dashicons-visibility"></span>
                <?php _e('Live Monitor', 'wp-oauth-debugger'); ?>
            </a>
            <a href="<?php echo admin_url('admin.php?page=oauth-debugger-security'); ?>" class="button button-secondary">
                <span class="dashicons dashicons-shield"></span>
                <?php _e('Security Analysis', 'wp-oauth-debugger'); ?>
            </a>
            <a href="#" class="button button-secondary oauth-debugger-clear-logs">
                <span class="dashicons dashicons-trash"></span>
                <?php _e('Clear Logs', 'wp-oauth-debugger'); ?>
            </a>
            <a href="<?php echo admin_url('admin.php?page=oauth-debugger-help'); ?>" class="button button-secondary">
                <span class="dashicons dashicons-editor-help"></span>
                <?php _e('View Documentation', 'wp-oauth-debugger'); ?>
            </a>
        </div>
    <?php
        $content = ob_get_clean();

        $this->render_card(
            __('Quick Actions', 'wp-oauth-debugger'),
            $content,
            array(
                'icon' => 'admin-tools'
            )
        );
    }

    /**
     * Render active tokens section
     *
     * @param array $tokens Active tokens data
     */
    private function render_active_tokens($tokens) {
        ob_start();
    ?>
        <?php if (empty($tokens)) : ?>
            <p><?php _e('No active tokens found.', 'wp-oauth-debugger'); ?></p>
        <?php else : ?>
            <ul class="oauth-debugger-tokens-list">
                <?php foreach ($tokens as $token) : ?>
                    <li>
                        <div class="oauth-debugger-token-info">
                            <?php echo esc_html($token['client_name'] ?? $token['client_id']); ?>
                        </div>
                        <div class="oauth-debugger-token-meta">
                            <span>
                                <?php _e('Created:', 'wp-oauth-debugger'); ?>
                                <?php echo esc_html(date_i18n('Y-m-d H:i', strtotime($token['created_at']))); ?>
                            </span>
                            <span>
                                <?php _e('Expires:', 'wp-oauth-debugger'); ?>
                                <?php
                                $expires = !empty($token['expires_at']) && $token['expires_at'] !== '0000-00-00 00:00:00'
                                    ? date_i18n('Y-m-d H:i', strtotime($token['expires_at']))
                                    : __('Never', 'wp-oauth-debugger');
                                echo esc_html($expires);
                                ?>
                            </span>
                        </div>
                        <div class="oauth-debugger-token-actions">
                            <a href="#" class="button button-small oauth-debugger-view-token" data-token-id="<?php echo esc_attr($token['id']); ?>">
                                <?php _e('View', 'wp-oauth-debugger'); ?>
                            </a>
                            <a href="#" class="button button-small oauth-debugger-delete-token" data-token-id="<?php echo esc_attr($token['id']); ?>">
                                <?php _e('Revoke', 'wp-oauth-debugger'); ?>
                            </a>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    <?php
        $content = ob_get_clean();

        $footer = '<a href="' . admin_url('admin.php?page=oauth-debugger-monitor#tokens') . '" class="button button-secondary">' .
            __('View All Tokens', 'wp-oauth-debugger') . '</a>';

        $this->render_card(
            __('Active Tokens', 'wp-oauth-debugger'),
            $content,
            array(
                'icon' => 'admin-network',
                'footer' => $footer
            )
        );
    }

    /**
     * Render security status section
     *
     * @param array $security_status Security status data
     */
    private function render_security_status($security_status) {
        $status_class = $security_status['status'] ?? 'secure';
        $status_icon = 'yes-alt';
        $status_text = __('Secure', 'wp-oauth-debugger');

        if ($status_class === 'warning') {
            $status_icon = 'warning';
            $status_text = __('Warning', 'wp-oauth-debugger');
        } elseif ($status_class === 'danger') {
            $status_icon = 'dismiss';
            $status_text = __('Vulnerable', 'wp-oauth-debugger');
        }

        ob_start();
    ?>
        <div class="oauth-debugger-security-status <?php echo esc_attr($status_class); ?>">
            <span class="dashicons dashicons-<?php echo esc_attr($status_icon); ?>"></span>
            <strong><?php echo esc_html($status_text); ?></strong>
        </div>

        <?php if (!empty($security_status['issues'])) : ?>
            <ul class="oauth-debugger-security-issues">
                <?php foreach ($security_status['issues'] as $issue) : ?>
                    <li>
                        <span class="oauth-debugger-security-issue-icon dashicons dashicons-<?php echo esc_attr($issue['severity'] === 'high' ? 'warning' : 'info'); ?>"></span>
                        <div>
                            <strong><?php echo esc_html($issue['title']); ?></strong>
                            <p><?php echo esc_html($issue['description']); ?></p>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else : ?>
            <p><?php _e('No security issues found.', 'wp-oauth-debugger'); ?></p>
        <?php endif; ?>
<?php
        $content = ob_get_clean();

        $footer = '<a href="' . admin_url('admin.php?page=oauth-debugger-security') . '" class="button button-secondary">' .
            '<span class="dashicons dashicons-shield"></span> ' .
            __('Full Security Analysis', 'wp-oauth-debugger') . '</a>';

        $this->render_card(
            __('Security Status', 'wp-oauth-debugger'),
            $content,
            array(
                'icon' => 'shield',
                'footer' => $footer
            )
        );
    }
}
