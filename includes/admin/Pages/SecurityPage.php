<?php

namespace WP_OAuth_Debugger\Admin\Pages;

use WP_OAuth_Debugger\Debug\DebugHelper;
use WP_OAuth_Debugger\Security\SecurityChecker;

/**
 * Security analysis page
 */
class SecurityPage extends BasePage {
    /**
     * @var DebugHelper
     */
    private $debug_helper;

    /**
     * @var SecurityChecker
     */
    private $security_checker;

    /**
     * Constructor
     */
    public function __construct() {
        $this->debug_helper = new DebugHelper();
        $this->security_checker = new SecurityChecker();
    }

    /**
     * Render the page
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
        return __('OAuth Security Analysis', 'wp-oauth-debugger');
    }

    /**
     * Get the page icon
     *
     * @return string
     */
    protected function get_page_icon() {
        return 'shield';
    }

    /**
     * Render header actions
     */
    protected function render_header_actions() {
?>
        <button type="button" class="button button-primary" id="oauth-debugger-run-scan">
            <span class="dashicons dashicons-shield"></span>
            <?php _e('Run New Scan', 'wp-oauth-debugger'); ?>
        </button>
        <a href="<?php echo admin_url('admin.php?page=oauth-debugger-settings&tab=security'); ?>" class="button button-secondary">
            <span class="dashicons dashicons-admin-settings"></span>
            <?php _e('Security Settings', 'wp-oauth-debugger'); ?>
        </a>
    <?php
    }

    /**
     * Render the main content
     */
    private function render_content() {
        // Get security status from both helpers for comprehensive information
        $security_status = $this->security_checker->get_security_status();

        // Get recent security logs from debug helper
        $security_logs = $this->debug_helper->get_recent_logs(10);
        $security_logs = array_filter($security_logs, function ($log) {
            return isset($log['level']) && ($log['level'] === 'warning' || $log['level'] === 'error');
        });

        $security_issues = $this->security_checker->get_security_issues();
        $vulnerability_count = count($security_issues);
        $high_severity_count = 0;
        $medium_severity_count = 0;
        $low_severity_count = 0;

        foreach ($security_issues as $issue) {
            if ($issue['severity'] === 'high') {
                $high_severity_count++;
            } elseif ($issue['severity'] === 'medium') {
                $medium_severity_count++;
            } else {
                $low_severity_count++;
            }
        }

        $status_class = 'secure';
        $status_icon = 'yes-alt';
        $status_text = __('Secure', 'wp-oauth-debugger');

        if ($high_severity_count > 0) {
            $status_class = 'danger';
            $status_icon = 'dismiss';
            $status_text = __('Vulnerable', 'wp-oauth-debugger');
        } elseif ($medium_severity_count > 0) {
            $status_class = 'warning';
            $status_icon = 'warning';
            $status_text = __('Warning', 'wp-oauth-debugger');
        } elseif ($low_severity_count > 0) {
            $status_class = 'info';
            $status_icon = 'info';
            $status_text = __('Notice', 'wp-oauth-debugger');
        }

        $last_scan = isset($security_status['last_scan']) ? date_i18n('Y-m-d H:i:s', strtotime($security_status['last_scan'])) : __('Never', 'wp-oauth-debugger');
    ?>
        <div class="oauth-debugger-security-dashboard">
            <div class="oauth-debugger-security-status-card <?php echo esc_attr($status_class); ?>">
                <div class="oauth-debugger-security-status-icon">
                    <span class="dashicons dashicons-<?php echo esc_attr($status_icon); ?>"></span>
                </div>
                <div class="oauth-debugger-security-status-content">
                    <h2><?php echo esc_html($status_text); ?></h2>
                    <p>
                        <?php
                        if ($vulnerability_count === 0) {
                            _e('No security issues found.', 'wp-oauth-debugger');
                        } else {
                            echo sprintf(
                                _n(
                                    '%d security issue found.',
                                    '%d security issues found.',
                                    $vulnerability_count,
                                    'wp-oauth-debugger'
                                ),
                                $vulnerability_count
                            );
                        }
                        ?>
                    </p>
                    <div class="oauth-debugger-security-status-meta">
                        <span><?php _e('Last scan:', 'wp-oauth-debugger'); ?> <?php echo esc_html($last_scan); ?></span>
                    </div>
                </div>
            </div>

            <div class="oauth-debugger-security-stats">
                <div class="oauth-debugger-security-stat-card">
                    <div class="oauth-debugger-security-stat">
                        <h3><?php _e('High Severity', 'wp-oauth-debugger'); ?></h3>
                        <p class="high"><?php echo esc_html(number_format_i18n($high_severity_count)); ?></p>
                    </div>
                </div>
                <div class="oauth-debugger-security-stat-card">
                    <div class="oauth-debugger-security-stat">
                        <h3><?php _e('Medium Severity', 'wp-oauth-debugger'); ?></h3>
                        <p class="medium"><?php echo esc_html(number_format_i18n($medium_severity_count)); ?></p>
                    </div>
                </div>
                <div class="oauth-debugger-security-stat-card">
                    <div class="oauth-debugger-security-stat">
                        <h3><?php _e('Low Severity', 'wp-oauth-debugger'); ?></h3>
                        <p class="low"><?php echo esc_html(number_format_i18n($low_severity_count)); ?></p>
                    </div>
                </div>
                <div class="oauth-debugger-security-stat-card">
                    <div class="oauth-debugger-security-stat">
                        <h3><?php _e('Failed Logins (24h)', 'wp-oauth-debugger'); ?></h3>
                        <p><?php echo esc_html(number_format_i18n($security_status['failed_logins'] ?? 0)); ?></p>
                    </div>
                </div>
            </div>

            <div class="oauth-debugger-security-issues">
                <div class="oauth-debugger-card">
                    <div class="oauth-debugger-card-header">
                        <h2>
                            <span class="dashicons dashicons-shield"></span>
                            <?php _e('Security Issues', 'wp-oauth-debugger'); ?>
                        </h2>
                    </div>
                    <div class="oauth-debugger-card-body">
                        <?php if (empty($security_issues)) : ?>
                            <div class="oauth-debugger-empty-state">
                                <div class="oauth-debugger-empty-state-icon">
                                    <span class="dashicons dashicons-yes-alt"></span>
                                </div>
                                <h3><?php _e('No security issues found', 'wp-oauth-debugger'); ?></h3>
                                <p><?php _e('Your OAuth implementation appears to be secure.', 'wp-oauth-debugger'); ?></p>
                            </div>
                        <?php else : ?>
                            <div class="oauth-debugger-issues-list">
                                <?php foreach ($security_issues as $issue) : ?>
                                    <div class="oauth-debugger-issue-item severity-<?php echo esc_attr($issue['severity']); ?>">
                                        <div class="oauth-debugger-issue-header">
                                            <h3>
                                                <span class="dashicons dashicons-<?php echo esc_attr($issue['severity'] === 'high' ? 'warning' : ($issue['severity'] === 'medium' ? 'flag' : 'info')); ?>"></span>
                                                <?php echo esc_html($issue['title']); ?>
                                            </h3>
                                            <span class="oauth-debugger-issue-severity"><?php echo esc_html(ucfirst($issue['severity'])); ?></span>
                                        </div>
                                        <div class="oauth-debugger-issue-details">
                                            <p><?php echo esc_html($issue['description']); ?></p>
                                            <?php if (!empty($issue['recommendation'])) : ?>
                                                <div class="oauth-debugger-issue-recommendation">
                                                    <h4><?php _e('Recommendation:', 'wp-oauth-debugger'); ?></h4>
                                                    <p><?php echo esc_html($issue['recommendation']); ?></p>
                                                </div>
                                            <?php endif; ?>
                                            <?php if (!empty($issue['reference'])) : ?>
                                                <div class="oauth-debugger-issue-reference">
                                                    <a href="<?php echo esc_url($issue['reference']); ?>" target="_blank">
                                                        <span class="dashicons dashicons-external"></span>
                                                        <?php _e('Learn more', 'wp-oauth-debugger'); ?>
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="oauth-debugger-security-recommendations">
                <div class="oauth-debugger-card">
                    <div class="oauth-debugger-card-header">
                        <h2>
                            <span class="dashicons dashicons-lightbulb"></span>
                            <?php _e('Best Practices', 'wp-oauth-debugger'); ?>
                        </h2>
                    </div>
                    <div class="oauth-debugger-card-body">
                        <div class="oauth-debugger-best-practices-list">
                            <div class="oauth-debugger-best-practice">
                                <h3><?php _e('Use HTTPS', 'wp-oauth-debugger'); ?></h3>
                                <p><?php _e('Always use HTTPS for all OAuth endpoints and redirects.', 'wp-oauth-debugger'); ?></p>
                            </div>
                            <div class="oauth-debugger-best-practice">
                                <h3><?php _e('Validate Redirect URIs', 'wp-oauth-debugger'); ?></h3>
                                <p><?php _e('Strictly validate redirect URIs against pre-registered values.', 'wp-oauth-debugger'); ?></p>
                            </div>
                            <div class="oauth-debugger-best-practice">
                                <h3><?php _e('Use PKCE for Public Clients', 'wp-oauth-debugger'); ?></h3>
                                <p><?php _e('Implement PKCE (Proof Key for Code Exchange) for all public clients.', 'wp-oauth-debugger'); ?></p>
                            </div>
                            <div class="oauth-debugger-best-practice">
                                <h3><?php _e('Short-lived Access Tokens', 'wp-oauth-debugger'); ?></h3>
                                <p><?php _e('Use short-lived access tokens and rotate them frequently.', 'wp-oauth-debugger'); ?></p>
                            </div>
                            <div class="oauth-debugger-best-practice">
                                <h3><?php _e('Validate State Parameter', 'wp-oauth-debugger'); ?></h3>
                                <p><?php _e('Always validate the state parameter to prevent CSRF attacks.', 'wp-oauth-debugger'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <style>
            .oauth-debugger-security-dashboard {
                display: grid;
                grid-template-columns: 1fr;
                gap: 20px;
                margin-top: 20px;
            }

            .oauth-debugger-security-status-card {
                display: flex;
                align-items: center;
                padding: 20px;
                border-radius: 4px;
                background-color: #fff;
                border-left: 5px solid #ccc;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            }

            .oauth-debugger-security-status-card.secure {
                border-left-color: #46b450;
                background-color: #f0fff1;
            }

            .oauth-debugger-security-status-card.info {
                border-left-color: #00a0d2;
                background-color: #f0f8ff;
            }

            .oauth-debugger-security-status-card.warning {
                border-left-color: #ffb900;
                background-color: #fff8e5;
            }

            .oauth-debugger-security-status-card.danger {
                border-left-color: #dc3232;
                background-color: #fef7f7;
            }

            .oauth-debugger-security-status-icon {
                margin-right: 20px;
            }

            .oauth-debugger-security-status-icon .dashicons {
                font-size: 48px;
                width: 48px;
                height: 48px;
            }

            .oauth-debugger-security-status-card.secure .oauth-debugger-security-status-icon .dashicons {
                color: #46b450;
            }

            .oauth-debugger-security-status-card.info .oauth-debugger-security-status-icon .dashicons {
                color: #00a0d2;
            }

            .oauth-debugger-security-status-card.warning .oauth-debugger-security-status-icon .dashicons {
                color: #ffb900;
            }

            .oauth-debugger-security-status-card.danger .oauth-debugger-security-status-icon .dashicons {
                color: #dc3232;
            }

            .oauth-debugger-security-status-content {
                flex: 1;
            }

            .oauth-debugger-security-status-content h2 {
                margin: 0 0 5px;
                font-size: 24px;
                font-weight: 500;
            }

            .oauth-debugger-security-status-content p {
                margin: 0 0 10px;
                font-size: 16px;
            }

            .oauth-debugger-security-status-meta {
                font-size: 12px;
                color: #666;
            }

            .oauth-debugger-security-stats {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 20px;
            }

            .oauth-debugger-security-stat-card {
                background-color: #fff;
                border-radius: 4px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                overflow: hidden;
            }

            .oauth-debugger-security-stat {
                padding: 15px;
                text-align: center;
            }

            .oauth-debugger-security-stat h3 {
                margin: 0 0 10px;
                font-size: 14px;
                color: #666;
                font-weight: normal;
            }

            .oauth-debugger-security-stat p {
                margin: 0;
                font-size: 36px;
                font-weight: 600;
                color: #2271b1;
            }

            .oauth-debugger-security-stat p.high {
                color: #dc3232;
            }

            .oauth-debugger-security-stat p.medium {
                color: #ffb900;
            }

            .oauth-debugger-security-stat p.low {
                color: #00a0d2;
            }

            .oauth-debugger-empty-state {
                text-align: center;
                padding: 40px 20px;
            }

            .oauth-debugger-empty-state-icon {
                margin-bottom: 15px;
            }

            .oauth-debugger-empty-state-icon .dashicons {
                font-size: 48px;
                width: 48px;
                height: 48px;
                color: #46b450;
            }

            .oauth-debugger-empty-state h3 {
                margin: 0 0 10px;
                font-size: 18px;
            }

            .oauth-debugger-empty-state p {
                margin: 0;
                color: #666;
            }

            .oauth-debugger-issues-list {
                margin: 0;
                padding: 0;
            }

            .oauth-debugger-issue-item {
                margin-bottom: 20px;
                padding-bottom: 20px;
                border-bottom: 1px solid #eee;
            }

            .oauth-debugger-issue-item:last-child {
                margin-bottom: 0;
                padding-bottom: 0;
                border-bottom: none;
            }

            .oauth-debugger-issue-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 10px;
            }

            .oauth-debugger-issue-header h3 {
                margin: 0;
                font-size: 16px;
                display: flex;
                align-items: center;
                gap: 5px;
            }

            .oauth-debugger-issue-severity {
                display: inline-block;
                padding: 2px 8px;
                border-radius: 3px;
                font-size: 11px;
                text-transform: uppercase;
                font-weight: 500;
                color: #fff;
                background-color: #666;
            }

            .oauth-debugger-issue-item.severity-high .oauth-debugger-issue-severity {
                background-color: #dc3232;
            }

            .oauth-debugger-issue-item.severity-medium .oauth-debugger-issue-severity {
                background-color: #ffb900;
            }

            .oauth-debugger-issue-item.severity-low .oauth-debugger-issue-severity {
                background-color: #00a0d2;
            }

            .oauth-debugger-issue-details p {
                margin: 0 0 15px;
            }

            .oauth-debugger-issue-recommendation {
                background-color: #f8f9fa;
                padding: 15px;
                border-radius: 4px;
                margin-bottom: 15px;
            }

            .oauth-debugger-issue-recommendation h4 {
                margin: 0 0 10px;
                font-size: 14px;
            }

            .oauth-debugger-issue-recommendation p {
                margin: 0;
            }

            .oauth-debugger-issue-reference {
                margin-top: 10px;
            }

            .oauth-debugger-issue-reference a {
                display: inline-flex;
                align-items: center;
                gap: 5px;
                color: #2271b1;
                text-decoration: none;
            }

            .oauth-debugger-issue-reference a:hover {
                color: #135e96;
                text-decoration: underline;
            }

            .oauth-debugger-best-practices-list {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 20px;
            }

            .oauth-debugger-best-practice {
                background-color: #f8f9fa;
                padding: 15px;
                border-radius: 4px;
                border-left: 3px solid #2271b1;
            }

            .oauth-debugger-best-practice h3 {
                margin: 0 0 10px;
                font-size: 16px;
            }

            .oauth-debugger-best-practice p {
                margin: 0;
                color: #666;
            }

            @media screen and (max-width: 782px) {

                .oauth-debugger-security-stats,
                .oauth-debugger-best-practices-list {
                    grid-template-columns: 1fr;
                }
            }
        </style>
<?php
    }
}
