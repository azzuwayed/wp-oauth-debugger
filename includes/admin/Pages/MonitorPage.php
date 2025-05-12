<?php

namespace WP_OAuth_Debugger\Admin\Pages;

use WP_OAuth_Debugger\Debug\DebugHelper;

/**
 * Live monitor page
 */
class MonitorPage extends BasePage {
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
        return __('OAuth Live Monitor', 'wp-oauth-debugger');
    }

    /**
     * Get the page icon
     *
     * @return string
     */
    protected function get_page_icon() {
        return 'visibility';
    }

    /**
     * Render header actions
     */
    protected function render_header_actions() {
?>
        <div class="oauth-debugger-refresh-controls">
            <button type="button" class="button button-secondary" id="oauth-debugger-refresh">
                <span class="dashicons dashicons-update"></span>
                <?php _e('Refresh', 'wp-oauth-debugger'); ?>
            </button>
            <label for="oauth-debugger-auto-refresh" class="oauth-debugger-auto-refresh-label">
                <input type="checkbox" id="oauth-debugger-auto-refresh" name="oauth-debugger-auto-refresh">
                <?php _e('Auto-refresh', 'wp-oauth-debugger'); ?>
                <select id="oauth-debugger-refresh-interval">
                    <option value="5"><?php _e('5 seconds', 'wp-oauth-debugger'); ?></option>
                    <option value="10" selected><?php _e('10 seconds', 'wp-oauth-debugger'); ?></option>
                    <option value="30"><?php _e('30 seconds', 'wp-oauth-debugger'); ?></option>
                    <option value="60"><?php _e('1 minute', 'wp-oauth-debugger'); ?></option>
                </select>
            </label>
        </div>
    <?php
    }

    /**
     * Render the main content
     */
    private function render_content() {
        $active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'logs';
    ?>
        <div class="oauth-debugger-tabs-wrapper">
            <nav class="oauth-debugger-tabs-nav">
                <ul>
                    <li class="<?php echo $active_tab === 'logs' ? 'active' : ''; ?>">
                        <a href="<?php echo admin_url('admin.php?page=oauth-debugger-monitor&tab=logs'); ?>">
                            <span class="dashicons dashicons-list-view"></span>
                            <?php _e('Logs', 'wp-oauth-debugger'); ?>
                        </a>
                    </li>
                    <li class="<?php echo $active_tab === 'tokens' ? 'active' : ''; ?>">
                        <a href="<?php echo admin_url('admin.php?page=oauth-debugger-monitor&tab=tokens'); ?>">
                            <span class="dashicons dashicons-admin-network"></span>
                            <?php _e('Active Tokens', 'wp-oauth-debugger'); ?>
                        </a>
                    </li>
                    <li class="<?php echo $active_tab === 'timeline' ? 'active' : ''; ?>">
                        <a href="<?php echo admin_url('admin.php?page=oauth-debugger-monitor&tab=timeline'); ?>">
                            <span class="dashicons dashicons-chart-line"></span>
                            <?php _e('Timeline', 'wp-oauth-debugger'); ?>
                        </a>
                    </li>
                    <li class="<?php echo $active_tab === 'realtime' ? 'active' : ''; ?>">
                        <a href="<?php echo admin_url('admin.php?page=oauth-debugger-monitor&tab=realtime'); ?>">
                            <span class="dashicons dashicons-chart-area"></span>
                            <?php _e('Real-time', 'wp-oauth-debugger'); ?>
                        </a>
                    </li>
                </ul>
            </nav>

            <div class="oauth-debugger-tabs-content">
                <?php
                switch ($active_tab) {
                    case 'logs':
                        $this->render_logs_tab();
                        break;
                    case 'tokens':
                        $this->render_tokens_tab();
                        break;
                    case 'timeline':
                        $this->render_timeline_tab();
                        break;
                    case 'realtime':
                        $this->render_realtime_tab();
                        break;
                    default:
                        $this->render_logs_tab();
                        break;
                }
                ?>
            </div>
        </div>

        <div id="oauth-debugger-log-detail-modal" class="oauth-debugger-modal">
            <div class="oauth-debugger-modal-content">
                <div class="oauth-debugger-modal-header">
                    <h2><?php _e('Log Entry Details', 'wp-oauth-debugger'); ?></h2>
                    <button type="button" class="oauth-debugger-modal-close">&times;</button>
                </div>
                <div class="oauth-debugger-modal-body">
                    <div id="oauth-debugger-log-detail-content"></div>
                </div>
                <div class="oauth-debugger-modal-footer">
                    <button type="button" class="button oauth-debugger-modal-close"><?php _e('Close', 'wp-oauth-debugger'); ?></button>
                </div>
            </div>
        </div>

        <div id="oauth-debugger-token-detail-modal" class="oauth-debugger-modal">
            <div class="oauth-debugger-modal-content">
                <div class="oauth-debugger-modal-header">
                    <h2><?php _e('Token Details', 'wp-oauth-debugger'); ?></h2>
                    <button type="button" class="oauth-debugger-modal-close">&times;</button>
                </div>
                <div class="oauth-debugger-modal-body">
                    <div id="oauth-debugger-token-detail-content"></div>
                </div>
                <div class="oauth-debugger-modal-footer">
                    <button type="button" class="button oauth-debugger-modal-close"><?php _e('Close', 'wp-oauth-debugger'); ?></button>
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

            .oauth-debugger-header-actions {
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .oauth-debugger-refresh-controls {
                display: flex;
                align-items: center;
                gap: 15px;
            }

            .oauth-debugger-auto-refresh-label {
                display: flex;
                align-items: center;
                gap: 5px;
            }

            .oauth-debugger-filters {
                display: flex;
                flex-wrap: wrap;
                gap: 15px;
                margin-bottom: 15px;
                padding: 10px 15px;
                background-color: #f8f9fa;
                border: 1px solid #ddd;
                border-radius: 4px;
            }

            .oauth-debugger-filter-group {
                display: flex;
                align-items: center;
                gap: 5px;
            }

            .oauth-debugger-filter-group label {
                margin-right: 5px;
                font-weight: 500;
            }

            .oauth-debugger-log-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }

            .oauth-debugger-log-table th,
            .oauth-debugger-log-table td {
                padding: 10px;
                text-align: left;
                border-bottom: 1px solid #eee;
            }

            .oauth-debugger-log-table th {
                background-color: #f8f9fa;
                font-weight: 500;
            }

            .oauth-debugger-log-table tr:hover {
                background-color: #f8f9fa;
                cursor: pointer;
            }

            .oauth-debugger-log-table .column-time {
                width: 150px;
            }

            .oauth-debugger-log-table .column-level {
                width: 100px;
            }

            .oauth-debugger-log-table .column-client {
                width: 150px;
            }

            .oauth-debugger-log-table .column-actions {
                width: 80px;
                text-align: right;
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

            .oauth-debugger-log-level.debug {
                background-color: #f0f0f1;
                color: #50575e;
            }

            .oauth-debugger-log-actions {
                display: flex;
                justify-content: flex-end;
                gap: 5px;
            }

            .oauth-debugger-log-actions button {
                padding: 0;
                width: 24px;
                height: 24px;
                border-radius: 50%;
                cursor: pointer;
                background: transparent;
                border: 1px solid #ddd;
                color: #666;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .oauth-debugger-log-actions button:hover {
                background-color: #f0f0f1;
                color: #0073aa;
            }

            .oauth-debugger-token-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }

            .oauth-debugger-token-table th,
            .oauth-debugger-token-table td {
                padding: 10px;
                text-align: left;
                border-bottom: 1px solid #eee;
            }

            .oauth-debugger-token-table th {
                background-color: #f8f9fa;
                font-weight: 500;
            }

            .oauth-debugger-token-table tr:hover {
                background-color: #f8f9fa;
                cursor: pointer;
            }

            .oauth-debugger-token-table .column-client {
                width: 200px;
            }

            .oauth-debugger-token-table .column-scopes {
                width: 200px;
            }

            .oauth-debugger-token-table .column-created {
                width: 150px;
            }

            .oauth-debugger-token-table .column-expires {
                width: 150px;
            }

            .oauth-debugger-token-table .column-actions {
                width: 120px;
                text-align: right;
            }

            .oauth-debugger-token-scopes {
                display: flex;
                flex-wrap: wrap;
                gap: 5px;
                max-width: 100%;
            }

            .oauth-debugger-token-scope {
                display: inline-block;
                padding: 2px 6px;
                border-radius: 3px;
                font-size: 11px;
                background-color: #f0f0f1;
                color: #50575e;
                white-space: nowrap;
            }

            .oauth-debugger-token-actions {
                display: flex;
                justify-content: flex-end;
                gap: 5px;
            }

            .oauth-debugger-token-actions button {
                padding: 0;
                background: transparent;
                border: none;
                cursor: pointer;
                color: #666;
                display: flex;
                align-items: center;
                gap: 3px;
            }

            .oauth-debugger-token-actions button:hover {
                color: #0073aa;
            }

            .oauth-debugger-token-actions .view {
                color: #0073aa;
            }

            .oauth-debugger-token-actions .revoke {
                color: #dc3232;
            }

            .oauth-debugger-timeline-container {
                height: 600px;
                border: 1px solid #ddd;
                border-radius: 4px;
                overflow: hidden;
                margin-bottom: 20px;
            }

            .oauth-debugger-realtime-container {
                height: 400px;
                border: 1px solid #ddd;
                border-radius: 4px;
                overflow: hidden;
                margin-bottom: 20px;
            }

            .oauth-debugger-realtime-stats {
                display: flex;
                flex-wrap: wrap;
                gap: 20px;
                margin-bottom: 20px;
            }

            .oauth-debugger-realtime-stat {
                flex: 1;
                min-width: 150px;
                background-color: #fff;
                border: 1px solid #ddd;
                border-radius: 4px;
                padding: 15px;
                text-align: center;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            }

            .oauth-debugger-realtime-stat h3 {
                margin: 0 0 10px;
                font-size: 14px;
                color: #666;
            }

            .oauth-debugger-realtime-stat p {
                margin: 0;
                font-size: 24px;
                font-weight: 600;
                color: #2271b1;
            }

            .oauth-debugger-modal {
                display: none;
                position: fixed;
                z-index: 9999;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                overflow: auto;
                background-color: rgba(0, 0, 0, 0.5);
            }

            .oauth-debugger-modal-content {
                position: relative;
                background-color: #fff;
                margin: 50px auto;
                padding: 0;
                border-radius: 4px;
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
                width: 80%;
                max-width: 800px;
                animation: oauth-debugger-modal-appear 0.3s ease-out;
            }

            @keyframes oauth-debugger-modal-appear {
                from {
                    transform: translateY(-50px);
                    opacity: 0;
                }

                to {
                    transform: translateY(0);
                    opacity: 1;
                }
            }

            .oauth-debugger-modal-header {
                padding: 15px;
                border-bottom: 1px solid #ddd;
                display: flex;
                align-items: center;
                justify-content: space-between;
                background-color: #f8f9fa;
                border-top-left-radius: 4px;
                border-top-right-radius: 4px;
            }

            .oauth-debugger-modal-header h2 {
                margin: 0;
                font-size: 16px;
            }

            .oauth-debugger-modal-close {
                color: #666;
                background: transparent;
                border: none;
                font-size: 20px;
                font-weight: bold;
                cursor: pointer;
            }

            .oauth-debugger-modal-close:hover {
                color: #0073aa;
            }

            .oauth-debugger-modal-body {
                padding: 15px;
                max-height: 500px;
                overflow-y: auto;
            }

            .oauth-debugger-modal-footer {
                padding: 10px 15px;
                border-top: 1px solid #ddd;
                text-align: right;
                background-color: #f8f9fa;
                border-bottom-left-radius: 4px;
                border-bottom-right-radius: 4px;
            }

            .oauth-debugger-log-detail-header {
                margin-bottom: 15px;
                padding-bottom: 10px;
                border-bottom: 1px solid #eee;
            }

            .oauth-debugger-log-detail-header h3 {
                margin: 0 0 10px;
                font-size: 16px;
            }

            .oauth-debugger-log-detail-meta {
                display: flex;
                flex-wrap: wrap;
                gap: 15px;
                margin-bottom: 10px;
            }

            .oauth-debugger-log-detail-meta-item {
                display: flex;
                align-items: center;
                gap: 5px;
            }

            .oauth-debugger-log-detail-meta-label {
                font-weight: 500;
                color: #666;
            }

            .oauth-debugger-log-detail-context {
                background-color: #f8f9fa;
                padding: 15px;
                border-radius: 4px;
                border: 1px solid #eee;
                font-family: monospace;
                white-space: pre-wrap;
                margin-top: 15px;
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

                .oauth-debugger-refresh-controls {
                    flex-direction: column;
                    align-items: flex-start;
                }

                .oauth-debugger-modal-content {
                    width: 95%;
                    margin: 20px auto;
                }

                .oauth-debugger-log-table .column-time,
                .oauth-debugger-log-table .column-client,
                .oauth-debugger-log-table .column-actions {
                    display: none;
                }

                .oauth-debugger-token-table .column-scopes,
                .oauth-debugger-token-table .column-created,
                .oauth-debugger-token-table .column-expires {
                    display: none;
                }
            }
        </style>
    <?php
    }

    /**
     * Render the logs tab
     */
    private function render_logs_tab() {
        $logs = $this->debug_helper->get_logs();
    ?>
        <div class="oauth-debugger-filters">
            <div class="oauth-debugger-filter-group">
                <label for="oauth-debugger-log-level-filter"><?php _e('Level:', 'wp-oauth-debugger'); ?></label>
                <select id="oauth-debugger-log-level-filter">
                    <option value="all"><?php _e('All Levels', 'wp-oauth-debugger'); ?></option>
                    <option value="info"><?php _e('Info', 'wp-oauth-debugger'); ?></option>
                    <option value="warning"><?php _e('Warning', 'wp-oauth-debugger'); ?></option>
                    <option value="error"><?php _e('Error', 'wp-oauth-debugger'); ?></option>
                    <option value="debug"><?php _e('Debug', 'wp-oauth-debugger'); ?></option>
                </select>
            </div>
            <div class="oauth-debugger-filter-group">
                <label for="oauth-debugger-date-filter"><?php _e('Date:', 'wp-oauth-debugger'); ?></label>
                <select id="oauth-debugger-date-filter">
                    <option value="all"><?php _e('All Dates', 'wp-oauth-debugger'); ?></option>
                    <option value="today"><?php _e('Today', 'wp-oauth-debugger'); ?></option>
                    <option value="yesterday"><?php _e('Yesterday', 'wp-oauth-debugger'); ?></option>
                    <option value="week"><?php _e('Last 7 Days', 'wp-oauth-debugger'); ?></option>
                    <option value="month"><?php _e('Last 30 Days', 'wp-oauth-debugger'); ?></option>
                </select>
            </div>
            <div class="oauth-debugger-filter-group">
                <label for="oauth-debugger-client-filter"><?php _e('Client:', 'wp-oauth-debugger'); ?></label>
                <select id="oauth-debugger-client-filter">
                    <option value="all"><?php _e('All Clients', 'wp-oauth-debugger'); ?></option>
                    <?php
                    $clients = $this->debug_helper->get_unique_clients();
                    foreach ($clients as $client) {
                        echo '<option value="' . esc_attr($client) . '">' . esc_html($client) . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="oauth-debugger-filter-group">
                <button type="button" class="button" id="oauth-debugger-clear-logs">
                    <span class="dashicons dashicons-trash"></span>
                    <?php _e('Clear Logs', 'wp-oauth-debugger'); ?>
                </button>
            </div>
        </div>

        <div id="oauth-debugger-logs-container">
            <?php if (empty($logs)) : ?>
                <p><?php _e('No logs found.', 'wp-oauth-debugger'); ?></p>
            <?php else : ?>
                <table class="oauth-debugger-log-table">
                    <thead>
                        <tr>
                            <th class="column-time"><?php _e('Time', 'wp-oauth-debugger'); ?></th>
                            <th class="column-level"><?php _e('Level', 'wp-oauth-debugger'); ?></th>
                            <th class="column-message"><?php _e('Message', 'wp-oauth-debugger'); ?></th>
                            <th class="column-client"><?php _e('Client', 'wp-oauth-debugger'); ?></th>
                            <th class="column-actions"><?php _e('Actions', 'wp-oauth-debugger'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log) : ?>
                            <tr data-log-id="<?php echo esc_attr($log['id']); ?>">
                                <td class="column-time"><?php echo esc_html(date_i18n('Y-m-d H:i:s', strtotime($log['timestamp']))); ?></td>
                                <td class="column-level">
                                    <span class="oauth-debugger-log-level <?php echo esc_attr(strtolower($log['level'])); ?>">
                                        <?php echo esc_html($log['level']); ?>
                                    </span>
                                </td>
                                <td class="column-message"><?php echo esc_html($log['message']); ?></td>
                                <td class="column-client"><?php echo esc_html($log['client_id'] ?? '-'); ?></td>
                                <td class="column-actions">
                                    <div class="oauth-debugger-log-actions">
                                        <button type="button" class="oauth-debugger-view-log" data-log-id="<?php echo esc_attr($log['id']); ?>">
                                            <span class="dashicons dashicons-visibility"></span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    <?php
    }

    /**
     * Render the tokens tab
     */
    private function render_tokens_tab() {
        $tokens = $this->debug_helper->get_active_tokens();
    ?>
        <div class="oauth-debugger-filters">
            <div class="oauth-debugger-filter-group">
                <label for="oauth-debugger-token-client-filter"><?php _e('Client:', 'wp-oauth-debugger'); ?></label>
                <select id="oauth-debugger-token-client-filter">
                    <option value="all"><?php _e('All Clients', 'wp-oauth-debugger'); ?></option>
                    <?php
                    $clients = $this->debug_helper->get_unique_clients();
                    foreach ($clients as $client) {
                        echo '<option value="' . esc_attr($client) . '">' . esc_html($client) . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="oauth-debugger-filter-group">
                <label for="oauth-debugger-token-status-filter"><?php _e('Status:', 'wp-oauth-debugger'); ?></label>
                <select id="oauth-debugger-token-status-filter">
                    <option value="all"><?php _e('All Statuses', 'wp-oauth-debugger'); ?></option>
                    <option value="active"><?php _e('Active', 'wp-oauth-debugger'); ?></option>
                    <option value="expired"><?php _e('Expired', 'wp-oauth-debugger'); ?></option>
                </select>
            </div>
        </div>

        <div id="oauth-debugger-tokens-container">
            <?php if (empty($tokens)) : ?>
                <p><?php _e('No active tokens found.', 'wp-oauth-debugger'); ?></p>
            <?php else : ?>
                <table class="oauth-debugger-token-table">
                    <thead>
                        <tr>
                            <th class="column-client"><?php _e('Client', 'wp-oauth-debugger'); ?></th>
                            <th class="column-type"><?php _e('Type', 'wp-oauth-debugger'); ?></th>
                            <th class="column-scopes"><?php _e('Scopes', 'wp-oauth-debugger'); ?></th>
                            <th class="column-created"><?php _e('Created', 'wp-oauth-debugger'); ?></th>
                            <th class="column-expires"><?php _e('Expires', 'wp-oauth-debugger'); ?></th>
                            <th class="column-actions"><?php _e('Actions', 'wp-oauth-debugger'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tokens as $token) : ?>
                            <tr data-token-id="<?php echo esc_attr($token['id']); ?>">
                                <td class="column-client"><?php echo esc_html($token['client_name'] ?? $token['client_id']); ?></td>
                                <td class="column-type"><?php echo esc_html(ucfirst($token['token_type'] ?? 'access')); ?></td>
                                <td class="column-scopes">
                                    <div class="oauth-debugger-token-scopes">
                                        <?php
                                        $scopes = isset($token['scopes']) ? explode(' ', $token['scopes']) : [];
                                        foreach ($scopes as $scope) :
                                        ?>
                                            <span class="oauth-debugger-token-scope"><?php echo esc_html($scope); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                                <td class="column-created"><?php echo esc_html(date_i18n('Y-m-d H:i', strtotime($token['created_at']))); ?></td>
                                <td class="column-expires">
                                    <?php
                                    if (!empty($token['expires_at']) && $token['expires_at'] !== '0000-00-00 00:00:00') {
                                        echo esc_html(date_i18n('Y-m-d H:i', strtotime($token['expires_at'])));
                                    } else {
                                        _e('Never', 'wp-oauth-debugger');
                                    }
                                    ?>
                                </td>
                                <td class="column-actions">
                                    <div class="oauth-debugger-token-actions">
                                        <button type="button" class="view oauth-debugger-view-token" data-token-id="<?php echo esc_attr($token['id']); ?>">
                                            <span class="dashicons dashicons-visibility"></span>
                                            <?php _e('View', 'wp-oauth-debugger'); ?>
                                        </button>
                                        <button type="button" class="revoke oauth-debugger-delete-token" data-token-id="<?php echo esc_attr($token['id']); ?>">
                                            <span class="dashicons dashicons-no"></span>
                                            <?php _e('Revoke', 'wp-oauth-debugger'); ?>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    <?php
    }

    /**
     * Render the timeline tab
     */
    private function render_timeline_tab() {
    ?>
        <div class="oauth-debugger-timeline-container" id="oauth-debugger-timeline">
            <div style="text-align: center; padding: 20px;">
                <?php _e('Loading timeline...', 'wp-oauth-debugger'); ?>
            </div>
        </div>
    <?php
    }

    /**
     * Render the realtime tab
     */
    private function render_realtime_tab() {
    ?>
        <div class="oauth-debugger-realtime-stats">
            <div class="oauth-debugger-realtime-stat">
                <h3><?php _e('Active Sessions', 'wp-oauth-debugger'); ?></h3>
                <p id="oauth-debugger-realtime-sessions">0</p>
            </div>
            <div class="oauth-debugger-realtime-stat">
                <h3><?php _e('Requests per Minute', 'wp-oauth-debugger'); ?></h3>
                <p id="oauth-debugger-realtime-rpm">0</p>
            </div>
            <div class="oauth-debugger-realtime-stat">
                <h3><?php _e('Error Rate', 'wp-oauth-debugger'); ?></h3>
                <p id="oauth-debugger-realtime-error-rate">0%</p>
            </div>
            <div class="oauth-debugger-realtime-stat">
                <h3><?php _e('Avg. Response Time', 'wp-oauth-debugger'); ?></h3>
                <p id="oauth-debugger-realtime-avg-time">0ms</p>
            </div>
        </div>

        <div class="oauth-debugger-realtime-container">
            <canvas id="oauth-debugger-realtime-chart"></canvas>
        </div>

        <div class="oauth-debugger-card">
            <div class="oauth-debugger-card-header">
                <h2>
                    <span class="dashicons dashicons-list-view"></span>
                    <?php _e('Live Events', 'wp-oauth-debugger'); ?>
                </h2>
            </div>
            <div class="oauth-debugger-card-body">
                <div id="oauth-debugger-realtime-events" style="max-height: 300px; overflow-y: auto;">
                    <p><?php _e('Waiting for events...', 'wp-oauth-debugger'); ?></p>
                </div>
            </div>
        </div>
<?php
    }
}
