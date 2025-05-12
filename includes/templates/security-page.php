<?php
if (!defined('ABSPATH')) {
    exit;
}

$security_status = $debug_helper->get_security_status();
$is_development = $security_status['environment'] === 'development';
?>
<div class="wrap oauth-debugger">
    <div class="oauth-debugger-header">
        <h1>
            <span class="dashicons dashicons-shield"></span>
            <?php _e('OAuth Security Analysis', 'wp-oauth-debugger'); ?>
        </h1>
        <div class="oauth-debugger-header-actions">
            <a href="<?php echo admin_url('admin.php?page=oauth-debugger'); ?>" class="button button-secondary">
                <span class="dashicons dashicons-arrow-left-alt"></span>
                <?php _e('Back to Debugger', 'wp-oauth-debugger'); ?>
            </a>
            <button class="button button-primary oauth-debugger-refresh-security" data-nonce="<?php echo wp_create_nonce('oauth_debug_nonce'); ?>">
                <span class="dashicons dashicons-update"></span>
                <?php _e('Refresh Analysis', 'wp-oauth-debugger'); ?>
            </button>
        </div>
    </div>

    <?php if ($is_development): ?>
        <div class="oauth-debugger-notice notice-warning">
            <div class="oauth-debugger-notice-content">
                <span class="dashicons dashicons-warning"></span>
                <div>
                    <strong><?php _e('Development Mode Active', 'wp-oauth-debugger'); ?></strong>
                    <p><?php _e('The security scanner is running in development mode. Some security checks and recommendations are specific to development environments.', 'wp-oauth-debugger'); ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="oauth-debugger-grid">
        <!-- Security Overview -->
        <div class="oauth-debugger-card oauth-debugger-security-overview">
            <div class="oauth-debugger-card-header">
                <h2>
                    <span class="dashicons dashicons-chart-bar"></span>
                    <?php _e('Security Overview', 'wp-oauth-debugger'); ?>
                </h2>
                <?php if ($is_development): ?>
                    <span class="oauth-debugger-badge info">
                        <span class="dashicons dashicons-code-standards"></span>
                        <?php _e('Development Environment', 'wp-oauth-debugger'); ?>
                    </span>
                <?php endif; ?>
            </div>
            <div class="oauth-debugger-card-body">
                <div class="oauth-debugger-security-score-container">
                    <?php
                    $score = $security_status['security_score'];
                    $score_class = $score >= 80 ? 'success' : ($score >= 60 ? 'warning' : 'error');
                    ?>
                    <div class="oauth-debugger-score-circle <?php echo $score_class; ?>">
                        <svg viewBox="0 0 36 36" class="oauth-debugger-score-chart">
                            <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#eee" stroke-width="3" />
                            <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                fill="none"
                                stroke="<?php echo $score_class === 'success' ? '#28a745' : ($score_class === 'warning' ? '#ffc107' : '#dc3545'); ?>"
                                stroke-width="3"
                                stroke-dasharray="<?php echo $score; ?>, 100" />
                        </svg>
                        <div class="oauth-debugger-score-content">
                            <span class="oauth-debugger-score-value"><?php echo $score; ?>%</span>
                            <span class="oauth-debugger-score-label"><?php _e('Security Score', 'wp-oauth-debugger'); ?></span>
                        </div>
                    </div>
                    <div class="oauth-debugger-security-metrics">
                        <div class="oauth-debugger-metric">
                            <span class="oauth-debugger-metric-value <?php echo $security_status['ssl_enabled'] ? 'success' : 'error'; ?>">
                                <span class="dashicons dashicons-<?php echo $security_status['ssl_enabled'] ? 'lock' : 'unlock'; ?>"></span>
                            </span>
                            <span class="oauth-debugger-metric-label"><?php _e('SSL', 'wp-oauth-debugger'); ?></span>
                        </div>
                        <div class="oauth-debugger-metric">
                            <span class="oauth-debugger-metric-value <?php echo $security_status['secure_cookies'] ? 'success' : 'warning'; ?>">
                                <span class="dashicons dashicons-<?php echo $security_status['secure_cookies'] ? 'yes' : 'warning'; ?>"></span>
                            </span>
                            <span class="oauth-debugger-metric-label"><?php _e('Cookies', 'wp-oauth-debugger'); ?></span>
                        </div>
                        <div class="oauth-debugger-metric">
                            <span class="oauth-debugger-metric-value <?php echo $security_status['pkce_support'] ? 'success' : 'warning'; ?>">
                                <span class="dashicons dashicons-<?php echo $security_status['pkce_support'] ? 'yes' : 'warning'; ?>"></span>
                            </span>
                            <span class="oauth-debugger-metric-label"><?php _e('PKCE', 'wp-oauth-debugger'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($is_development && !empty($security_status['development_checks'])): ?>
            <!-- Development Environment -->
            <div class="oauth-debugger-card">
                <div class="oauth-debugger-card-header">
                    <h2>
                        <span class="dashicons dashicons-code-standards"></span>
                        <?php _e('Development Environment', 'wp-oauth-debugger'); ?>
                    </h2>
                </div>
                <div class="oauth-debugger-card-body">
                    <div class="oauth-debugger-development-grid">
                        <!-- Debug Settings -->
                        <div class="oauth-debugger-section">
                            <h3>
                                <span class="dashicons dashicons-editor-code"></span>
                                <?php _e('Debug Settings', 'wp-oauth-debugger'); ?>
                            </h3>
                            <div class="oauth-debugger-check-list">
                                <?php foreach (['debug_mode', 'debug_log', 'debug_display', 'script_debug'] as $check): ?>
                                    <?php if (isset($security_status['development_checks'][$check])): ?>
                                        <div class="oauth-debugger-check-item">
                                            <div class="oauth-debugger-check-header">
                                                <span class="oauth-debugger-badge <?php echo $security_status['development_checks'][$check]['enabled'] ? 'warning' : 'success'; ?>">
                                                    <span class="dashicons dashicons-<?php echo $security_status['development_checks'][$check]['enabled'] ? 'warning' : 'yes'; ?>"></span>
                                                    <?php echo $security_status['development_checks'][$check]['enabled'] ? __('Enabled', 'wp-oauth-debugger') : __('Disabled', 'wp-oauth-debugger'); ?>
                                                </span>
                                                <h4><?php echo esc_html($security_status['development_checks'][$check]['description']); ?></h4>
                                            </div>
                                            <p class="description"><?php echo esc_html($security_status['development_checks'][$check]['details']); ?></p>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Local Environment -->
                        <div class="oauth-debugger-section">
                            <h3>
                                <span class="dashicons dashicons-admin-home"></span>
                                <?php _e('Local Environment', 'wp-oauth-debugger'); ?>
                            </h3>
                            <div class="oauth-debugger-check-list">
                                <?php foreach ($security_status['development_checks']['local_environment'] as $check): ?>
                                    <div class="oauth-debugger-check-item">
                                        <div class="oauth-debugger-check-header">
                                            <span class="oauth-debugger-badge <?php echo $check['is_local'] ? 'info' : 'success'; ?>">
                                                <span class="dashicons dashicons-<?php echo $check['is_local'] ? 'admin-home' : 'cloud'; ?>"></span>
                                                <?php echo $check['is_local'] ? __('Local', 'wp-oauth-debugger') : __('Production', 'wp-oauth-debugger'); ?>
                                            </span>
                                            <h4><?php echo esc_html($check['description']); ?></h4>
                                        </div>
                                        <p class="description"><?php echo esc_html($check['details']); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Development Tools -->
                        <div class="oauth-debugger-section">
                            <h3>
                                <span class="dashicons dashicons-admin-tools"></span>
                                <?php _e('Development Tools', 'wp-oauth-debugger'); ?>
                            </h3>
                            <div class="oauth-debugger-check-list">
                                <?php foreach ($security_status['development_checks']['development_tools'] as $tool): ?>
                                    <div class="oauth-debugger-check-item">
                                        <div class="oauth-debugger-check-header">
                                            <span class="oauth-debugger-badge <?php echo $tool['enabled'] ? 'warning' : 'success'; ?>">
                                                <span class="dashicons dashicons-<?php echo $tool['enabled'] ? 'warning' : 'yes'; ?>"></span>
                                                <?php echo $tool['enabled'] ? __('Enabled', 'wp-oauth-debugger') : __('Disabled', 'wp-oauth-debugger'); ?>
                                            </span>
                                            <h4><?php echo esc_html($tool['description']); ?></h4>
                                        </div>
                                        <p class="description"><?php echo esc_html($tool['details']); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Vulnerabilities -->
        <?php if (!empty($security_status['vulnerabilities'])): ?>
            <div class="oauth-debugger-card">
                <div class="oauth-debugger-card-header">
                    <h2>
                        <span class="dashicons dashicons-warning"></span>
                        <?php _e('Security Vulnerabilities', 'wp-oauth-debugger'); ?>
                    </h2>
                </div>
                <div class="oauth-debugger-card-body">
                    <div class="oauth-debugger-vulnerabilities-list">
                        <?php foreach ($security_status['vulnerabilities'] as $vulnerability): ?>
                            <div class="oauth-debugger-vulnerability-item oauth-debugger-severity-<?php echo $vulnerability['severity']; ?>">
                                <div class="oauth-debugger-vulnerability-header">
                                    <span class="oauth-debugger-badge <?php echo $vulnerability['severity']; ?>">
                                        <span class="dashicons dashicons-<?php
                                                                            echo $vulnerability['severity'] === 'critical' ? 'dismiss' : ($vulnerability['severity'] === 'high' ? 'warning' : ($vulnerability['severity'] === 'medium' ? 'info' : 'info'));
                                                                            ?>"></span>
                                        <?php echo ucfirst($vulnerability['severity']); ?>
                                    </span>
                                    <h3><?php echo esc_html($vulnerability['description']); ?></h3>
                                </div>
                                <div class="oauth-debugger-vulnerability-content">
                                    <p><?php echo esc_html($vulnerability['details']); ?></p>
                                    <?php if (!empty($vulnerability['solution'])): ?>
                                        <div class="oauth-debugger-vulnerability-solution">
                                            <strong><?php _e('Recommended Solution:', 'wp-oauth-debugger'); ?></strong>
                                            <p><?php echo esc_html($vulnerability['solution']); ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- JWT Analysis -->
        <?php if ($security_status['jwt_analysis']['jwt_enabled']): ?>
            <div class="oauth-debugger-card">
                <div class="oauth-debugger-card-header">
                    <h2>
                        <span class="dashicons dashicons-lock"></span>
                        <?php _e('JWT Analysis', 'wp-oauth-debugger'); ?>
                    </h2>
                </div>
                <div class="oauth-debugger-card-body">
                    <div class="oauth-debugger-jwt-grid">
                        <div class="oauth-debugger-jwt-item">
                            <h3><?php _e('Algorithm', 'wp-oauth-debugger'); ?></h3>
                            <span class="oauth-debugger-badge <?php echo $security_status['jwt_analysis']['algorithm'] === 'none' ? 'error' : 'success'; ?>">
                                <span class="dashicons dashicons-<?php echo $security_status['jwt_analysis']['algorithm'] === 'none' ? 'warning' : 'yes'; ?>"></span>
                                <?php echo esc_html($security_status['jwt_analysis']['algorithm']); ?>
                            </span>
                        </div>
                        <?php if (!empty($security_status['jwt_analysis']['vulnerabilities'])): ?>
                            <div class="oauth-debugger-jwt-vulnerabilities">
                                <h3><?php _e('JWT Vulnerabilities', 'wp-oauth-debugger'); ?></h3>
                                <div class="oauth-debugger-check-list">
                                    <?php foreach ($security_status['jwt_analysis']['vulnerabilities'] as $vulnerability): ?>
                                        <div class="oauth-debugger-check-item">
                                            <div class="oauth-debugger-check-header">
                                                <span class="oauth-debugger-badge <?php echo $vulnerability['severity']; ?>">
                                                    <span class="dashicons dashicons-warning"></span>
                                                    <?php echo ucfirst($vulnerability['severity']); ?>
                                                </span>
                                                <h4><?php echo esc_html($vulnerability['description']); ?></h4>
                                            </div>
                                            <p class="description"><?php echo esc_html($vulnerability['details']); ?></p>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Security Recommendations -->
        <?php if (!empty($security_status['recommendations'])): ?>
            <div class="oauth-debugger-card">
                <div class="oauth-debugger-card-header">
                    <h2>
                        <span class="dashicons dashicons-lightbulb"></span>
                        <?php _e('Security Recommendations', 'wp-oauth-debugger'); ?>
                    </h2>
                </div>
                <div class="oauth-debugger-card-body">
                    <div class="oauth-debugger-recommendations-list">
                        <?php foreach ($security_status['recommendations'] as $recommendation): ?>
                            <div class="oauth-debugger-recommendation-item">
                                <div class="oauth-debugger-recommendation-header">
                                    <span class="dashicons dashicons-lightbulb"></span>
                                    <h3><?php echo esc_html($recommendation['title']); ?></h3>
                                </div>
                                <div class="oauth-debugger-recommendation-content">
                                    <p class="oauth-debugger-recommendation-description">
                                        <?php echo esc_html($recommendation['description']); ?>
                                    </p>
                                    <div class="oauth-debugger-recommendation-solution">
                                        <strong><?php _e('Solution:', 'wp-oauth-debugger'); ?></strong>
                                        <p><?php echo esc_html($recommendation['solution']); ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .oauth-debugger-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin: 20px 0;
        padding-bottom: 20px;
        border-bottom: 1px solid #ddd;
    }

    .oauth-debugger-header h1 {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0;
    }

    .oauth-debugger-header-actions {
        display: flex;
        gap: 10px;
    }

    .oauth-debugger-notice {
        margin: 20px 0;
        padding: 0;
        border-left: none;
        background: transparent;
    }

    .oauth-debugger-notice-content {
        display: flex;
        gap: 15px;
        padding: 15px;
        background: #fff8e5;
        border-left: 4px solid #ffb900;
        border-radius: 4px;
    }

    .oauth-debugger-notice-content .dashicons {
        font-size: 24px;
        width: 24px;
        height: 24px;
        color: #ffb900;
    }

    .oauth-debugger-notice-content strong {
        display: block;
        margin-bottom: 5px;
        color: #1d2327;
    }

    .oauth-debugger-notice-content p {
        margin: 0;
        color: #50575e;
    }

    .oauth-debugger-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 20px;
        margin: 20px 0;
    }

    .oauth-debugger-card {
        background: #fff;
        border: 1px solid #ccd0d4;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    .oauth-debugger-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 20px;
        background: #f8f9fa;
        border-bottom: 1px solid #ddd;
    }

    .oauth-debugger-card-header h2 {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0;
        font-size: 1.2em;
    }

    .oauth-debugger-card-body {
        padding: 20px;
    }

    .oauth-debugger-security-score-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 20px;
    }

    .oauth-debugger-score-circle {
        position: relative;
        width: 150px;
        height: 150px;
    }

    .oauth-debugger-score-chart {
        transform: rotate(-90deg);
    }

    .oauth-debugger-score-content {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
    }

    .oauth-debugger-score-value {
        display: block;
        font-size: 2em;
        font-weight: bold;
        line-height: 1;
    }

    .oauth-debugger-score-label {
        display: block;
        font-size: 0.9em;
        color: #666;
    }

    .oauth-debugger-security-metrics {
        display: flex;
        justify-content: center;
        gap: 30px;
    }

    .oauth-debugger-metric {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 5px;
    }

    .oauth-debugger-metric-value {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        font-size: 20px;
    }

    .oauth-debugger-metric-value.success {
        background: #d4edda;
        color: #155724;
    }

    .oauth-debugger-metric-value.warning {
        background: #fff3cd;
        color: #856404;
    }

    .oauth-debugger-metric-value.error {
        background: #f8d7da;
        color: #721c24;
    }

    .oauth-debugger-metric-label {
        font-size: 0.9em;
        color: #666;
    }

    .oauth-debugger-development-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
    }

    .oauth-debugger-section h3 {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0 0 15px 0;
        font-size: 1.1em;
        color: #1d2327;
    }

    .oauth-debugger-check-list {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .oauth-debugger-check-item {
        background: #f8f9fa;
        border: 1px solid #ddd;
        border-radius: 6px;
        padding: 15px;
    }

    .oauth-debugger-check-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
    }

    .oauth-debugger-check-header h4 {
        margin: 0;
        font-size: 1em;
        color: #1d2327;
    }

    .oauth-debugger-check-item .description {
        margin: 0;
        font-size: 0.9em;
        color: #666;
    }

    .oauth-debugger-vulnerabilities-list {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .oauth-debugger-vulnerability-item {
        background: #f8f9fa;
        border: 1px solid #ddd;
        border-radius: 6px;
        padding: 15px;
    }

    .oauth-debugger-vulnerability-item.oauth-debugger-severity-critical {
        border-left: 4px solid #dc3545;
    }

    .oauth-debugger-vulnerability-item.oauth-debugger-severity-high {
        border-left: 4px solid #fd7e14;
    }

    .oauth-debugger-vulnerability-item.oauth-debugger-severity-medium {
        border-left: 4px solid #ffc107;
    }

    .oauth-debugger-vulnerability-item.oauth-debugger-severity-low {
        border-left: 4px solid #20c997;
    }

    .oauth-debugger-vulnerability-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
    }

    .oauth-debugger-vulnerability-header h3 {
        margin: 0;
        font-size: 1.1em;
        color: #1d2327;
    }

    .oauth-debugger-vulnerability-content p {
        margin: 0 0 10px 0;
        color: #50575e;
    }

    .oauth-debugger-vulnerability-solution {
        background: #e9ecef;
        padding: 10px;
        border-radius: 4px;
    }

    .oauth-debugger-vulnerability-solution strong {
        display: block;
        margin-bottom: 5px;
        color: #1d2327;
    }

    .oauth-debugger-vulnerability-solution p {
        margin: 0;
        color: #50575e;
    }

    .oauth-debugger-jwt-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }

    .oauth-debugger-jwt-item {
        background: #f8f9fa;
        border: 1px solid #ddd;
        border-radius: 6px;
        padding: 15px;
    }

    .oauth-debugger-jwt-item h3 {
        margin: 0 0 10px 0;
        font-size: 1em;
        color: #1d2327;
    }

    .oauth-debugger-recommendations-list {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .oauth-debugger-recommendation-item {
        background: #f8f9fa;
        border: 1px solid #ddd;
        border-radius: 6px;
        padding: 15px;
    }

    .oauth-debugger-recommendation-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
    }

    .oauth-debugger-recommendation-header .dashicons {
        color: #ffb900;
    }

    .oauth-debugger-recommendation-header h3 {
        margin: 0;
        font-size: 1.1em;
        color: #1d2327;
    }

    .oauth-debugger-recommendation-content p {
        margin: 0 0 10px 0;
        color: #50575e;
    }

    .oauth-debugger-recommendation-solution {
        background: #e9ecef;
        padding: 10px;
        border-radius: 4px;
    }

    .oauth-debugger-recommendation-solution strong {
        display: block;
        margin-bottom: 5px;
        color: #1d2327;
    }

    .oauth-debugger-recommendation-solution p {
        margin: 0;
        color: #50575e;
    }

    .oauth-debugger-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 0.9em;
        background: #e9ecef;
        color: #495057;
    }

    .oauth-debugger-badge.success {
        background: #d4edda;
        color: #155724;
    }

    .oauth-debugger-badge.warning {
        background: #fff3cd;
        color: #856404;
    }

    .oauth-debugger-badge.error {
        background: #f8d7da;
        color: #721c24;
    }

    .oauth-debugger-badge.info {
        background: #cce5ff;
        color: #004085;
    }

    .oauth-debugger-badge.critical {
        background: #dc3545;
        color: #fff;
    }

    .oauth-debugger-badge.high {
        background: #fd7e14;
        color: #fff;
    }

    .oauth-debugger-badge.medium {
        background: #ffc107;
        color: #000;
    }

    .oauth-debugger-badge.low {
        background: #20c997;
        color: #fff;
    }

    @media (max-width: 782px) {
        .oauth-debugger-header {
            flex-direction: column;
            gap: 15px;
            align-items: flex-start;
        }

        .oauth-debugger-header-actions {
            width: 100%;
            justify-content: space-between;
        }

        .oauth-debugger-grid {
            grid-template-columns: 1fr;
        }

        .oauth-debugger-security-metrics {
            flex-wrap: wrap;
        }

        .oauth-debugger-development-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<script>
    jQuery(document).ready(function($) {
        $('.oauth-debugger-refresh-security').on('click', function() {
            const button = $(this);
            const nonce = button.data('nonce');

            button.prop('disabled', true);
            button.find('.dashicons').addClass('spin');

            $.post(oauthDebug.ajaxurl, {
                action: 'oauth_debugger_get_updates',
                nonce: nonce
            }, function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data || oauthDebug.i18n.error);
                }
            }).fail(function() {
                alert(oauthDebug.i18n.error);
            }).always(function() {
                button.prop('disabled', false);
                button.find('.dashicons').removeClass('spin');
            });
        });
    });
</script>

<style>
    @keyframes spin {
        from {
            transform: rotate(0deg);
        }

        to {
            transform: rotate(360deg);
        }
    }

    .dashicons.spin {
        animation: spin 1s linear infinite;
    }
</style>
