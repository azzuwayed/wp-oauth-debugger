<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap oauth-debugger">
    <div class="oauth-debugger-header">
        <h1>
            <span class="dashicons dashicons-search"></span>
            <?php _e('OAuth Debugger', 'wp-oauth-debugger'); ?>
        </h1>
        <div class="oauth-debugger-header-actions">
            <a href="<?php echo admin_url('admin.php?page=oauth-debugger-monitor'); ?>" class="button button-primary">
                <span class="dashicons dashicons-visibility"></span>
                <?php _e('Live Monitor', 'wp-oauth-debugger'); ?>
            </a>
            <a href="<?php echo admin_url('admin.php?page=oauth-debugger-security'); ?>" class="button button-secondary">
                <span class="dashicons dashicons-shield"></span>
                <?php _e('Security Analysis', 'wp-oauth-debugger'); ?>
            </a>
        </div>
    </div>

    <div class="oauth-debugger-grid">
        <!-- Server Information -->
        <div class="oauth-debugger-card">
            <div class="oauth-debugger-card-header">
                <h2>
                    <span class="dashicons dashicons-info"></span>
                    <?php _e('Server Information', 'wp-oauth-debugger'); ?>
                </h2>
            </div>
            <div class="oauth-debugger-card-body">
                <div class="oauth-debugger-info-grid">
                    <div class="oauth-debugger-info-item">
                        <span class="oauth-debugger-info-label"><?php _e('WordPress Version', 'wp-oauth-debugger'); ?></span>
                        <span class="oauth-debugger-info-value"><?php echo esc_html($server_info['wordpress_version']); ?></span>
                    </div>
                    <div class="oauth-debugger-info-item">
                        <span class="oauth-debugger-info-label"><?php _e('PHP Version', 'wp-oauth-debugger'); ?></span>
                        <span class="oauth-debugger-info-value"><?php echo esc_html($server_info['php_version']); ?></span>
                    </div>
                    <div class="oauth-debugger-info-item">
                        <span class="oauth-debugger-info-label"><?php _e('Server Software', 'wp-oauth-debugger'); ?></span>
                        <span class="oauth-debugger-info-value"><?php echo esc_html($server_info['server_software']); ?></span>
                    </div>
                    <div class="oauth-debugger-info-item">
                        <span class="oauth-debugger-info-label"><?php _e('OAuth Server Version', 'wp-oauth-debugger'); ?></span>
                        <span class="oauth-debugger-info-value"><?php echo esc_html($server_info['oauth_server_version']); ?></span>
                    </div>
                    <div class="oauth-debugger-info-item">
                        <span class="oauth-debugger-info-label"><?php _e('SSL Status', 'wp-oauth-debugger'); ?></span>
                        <span class="oauth-debugger-badge <?php echo $server_info['ssl_enabled'] ? 'success' : 'error'; ?>">
                            <span class="dashicons dashicons-<?php echo $server_info['ssl_enabled'] ? 'lock' : 'unlock'; ?>"></span>
                            <?php echo $server_info['ssl_enabled'] ? __('Secure', 'wp-oauth-debugger') : __('Not Secure', 'wp-oauth-debugger'); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Security Overview -->
        <div class="oauth-debugger-card">
            <div class="oauth-debugger-card-header">
                <h2>
                    <span class="dashicons dashicons-shield"></span>
                    <?php _e('Security Overview', 'wp-oauth-debugger'); ?>
                </h2>
            </div>
            <div class="oauth-debugger-card-body">
                <div class="oauth-debugger-security-grid">
                    <div class="oauth-debugger-security-item">
                        <div class="oauth-debugger-security-icon <?php echo $security_status['ssl_enabled'] ? 'success' : 'error'; ?>">
                            <span class="dashicons dashicons-<?php echo $security_status['ssl_enabled'] ? 'lock' : 'unlock'; ?>"></span>
                        </div>
                        <div class="oauth-debugger-security-info">
                            <h3><?php _e('SSL Status', 'wp-oauth-debugger'); ?></h3>
                            <p><?php echo $security_status['ssl_enabled'] ? __('SSL is properly configured', 'wp-oauth-debugger') : __('SSL is not enabled', 'wp-oauth-debugger'); ?></p>
                        </div>
                    </div>
                    <div class="oauth-debugger-security-item">
                        <div class="oauth-debugger-security-icon <?php echo $security_status['secure_cookies'] ? 'success' : 'warning'; ?>">
                            <span class="dashicons dashicons-<?php echo $security_status['secure_cookies'] ? 'yes' : 'warning'; ?>"></span>
                        </div>
                        <div class="oauth-debugger-security-info">
                            <h3><?php _e('Cookie Security', 'wp-oauth-debugger'); ?></h3>
                            <p><?php echo $security_status['secure_cookies'] ? __('Secure cookies are enabled', 'wp-oauth-debugger') : __('Secure cookies are not enabled', 'wp-oauth-debugger'); ?></p>
                        </div>
                    </div>
                </div>
                <div class="oauth-debugger-card-footer">
                    <a href="<?php echo admin_url('admin.php?page=oauth-debugger-security'); ?>" class="button button-secondary">
                        <span class="dashicons dashicons-search"></span>
                        <?php _e('View Full Security Analysis', 'wp-oauth-debugger'); ?>
                    </a>
                </div>
            </div>
        </div>

        <!-- Active Tokens -->
        <div class="oauth-debugger-card">
            <div class="oauth-debugger-card-header">
                <h2>
                    <span class="dashicons dashicons-admin-users"></span>
                    <?php _e('Active Tokens', 'wp-oauth-debugger'); ?>
                </h2>
                <div class="oauth-debugger-card-actions">
                    <span class="oauth-debugger-count">
                        <span class="dashicons dashicons-groups"></span>
                        <?php echo count($active_tokens); ?> <?php _e('active', 'wp-oauth-debugger'); ?>
                    </span>
                </div>
            </div>
            <div class="oauth-debugger-card-body">
                <?php if (empty($active_tokens)): ?>
                    <div class="oauth-debugger-empty-state">
                        <span class="dashicons dashicons-admin-users"></span>
                        <p><?php _e('No active tokens found.', 'wp-oauth-debugger'); ?></p>
                    </div>
                <?php else: ?>
                    <div class="oauth-debugger-tokens-list">
                        <?php foreach ($active_tokens as $token): ?>
                            <div class="oauth-debugger-token-item">
                                <div class="oauth-debugger-token-header">
                                    <div class="oauth-debugger-token-user">
                                        <span class="dashicons dashicons-admin-users"></span>
                                        <strong><?php echo esc_html($token['user_login']); ?></strong>
                                    </div>
                                    <div class="oauth-debugger-token-client">
                                        <span class="dashicons dashicons-admin-generic"></span>
                                        <?php echo esc_html($token['client_id']); ?>
                                    </div>
                                </div>
                                <div class="oauth-debugger-token-scopes">
                                    <?php foreach ($token['scopes'] as $scope): ?>
                                        <span class="oauth-debugger-badge">
                                            <span class="dashicons dashicons-tag"></span>
                                            <?php echo esc_html($scope); ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                                <div class="oauth-debugger-token-details">
                                    <div class="oauth-debugger-token-time">
                                        <span class="oauth-debugger-token-created">
                                            <span class="dashicons dashicons-calendar-alt"></span>
                                            <?php _e('Created:', 'wp-oauth-debugger'); ?>
                                            <?php echo esc_html($token['created_at']); ?>
                                        </span>
                                        <span class="oauth-debugger-token-expires">
                                            <span class="dashicons dashicons-clock"></span>
                                            <?php _e('Expires:', 'wp-oauth-debugger'); ?>
                                            <?php echo esc_html($token['expires']); ?>
                                        </span>
                                    </div>
                                    <button class="button oauth-debugger-delete-token"
                                        data-token-id="<?php echo esc_attr($token['id']); ?>"
                                        data-nonce="<?php echo wp_create_nonce('oauth_debug_nonce'); ?>">
                                        <span class="dashicons dashicons-no-alt"></span>
                                        <?php _e('Revoke', 'wp-oauth-debugger'); ?>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if (defined('OAUTH_DEBUG') && OAUTH_DEBUG): ?>
        <!-- Debug Instructions -->
        <div class="oauth-debugger-card">
            <div class="oauth-debugger-card-header">
                <h2>
                    <span class="dashicons dashicons-editor-code"></span>
                    <?php _e('Debug Instructions', 'wp-oauth-debugger'); ?>
                </h2>
            </div>
            <div class="oauth-debugger-card-body">
                <div class="oauth-debugger-instructions">
                    <div class="oauth-debugger-code-block">
                        <h3><?php _e('Configuration', 'wp-oauth-debugger'); ?></h3>
                        <p><?php _e('Add the following to your wp-config.php file:', 'wp-oauth-debugger'); ?></p>
                        <pre><code>define('OAUTH_DEBUG', true);
define('OAUTH_DEBUG_LOG_LEVEL', 'debug'); // Options: debug, info, warning, error
define('OAUTH_DEBUG_LOG_RETENTION', 7); // Number of days to keep logs</code></pre>
                    </div>
                    <div class="oauth-debugger-log-levels">
                        <h3><?php _e('Log Levels', 'wp-oauth-debugger'); ?></h3>
                        <div class="oauth-debugger-log-level-grid">
                            <div class="oauth-debugger-log-level-item">
                                <span class="oauth-debugger-badge debug">debug</span>
                                <p><?php _e('Detailed debugging information', 'wp-oauth-debugger'); ?></p>
                            </div>
                            <div class="oauth-debugger-log-level-item">
                                <span class="oauth-debugger-badge info">info</span>
                                <p><?php _e('General information about OAuth operations', 'wp-oauth-debugger'); ?></p>
                            </div>
                            <div class="oauth-debugger-log-level-item">
                                <span class="oauth-debugger-badge warning">warning</span>
                                <p><?php _e('Warning messages about potential issues', 'wp-oauth-debugger'); ?></p>
                            </div>
                            <div class="oauth-debugger-log-level-item">
                                <span class="oauth-debugger-badge error">error</span>
                                <p><?php _e('Error messages about failed operations', 'wp-oauth-debugger'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
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

    .oauth-debugger-card-footer {
        padding: 15px 20px;
        background: #f8f9fa;
        border-top: 1px solid #ddd;
        text-align: right;
    }

    .oauth-debugger-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
    }

    .oauth-debugger-info-item {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .oauth-debugger-info-label {
        font-size: 0.9em;
        color: #666;
    }

    .oauth-debugger-info-value {
        font-weight: 500;
    }

    .oauth-debugger-security-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }

    .oauth-debugger-security-item {
        display: flex;
        align-items: flex-start;
        gap: 15px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 6px;
    }

    .oauth-debugger-security-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        font-size: 20px;
    }

    .oauth-debugger-security-icon.success {
        background: #d4edda;
        color: #155724;
    }

    .oauth-debugger-security-icon.warning {
        background: #fff3cd;
        color: #856404;
    }

    .oauth-debugger-security-icon.error {
        background: #f8d7da;
        color: #721c24;
    }

    .oauth-debugger-security-info h3 {
        margin: 0 0 5px 0;
        font-size: 1em;
    }

    .oauth-debugger-security-info p {
        margin: 0;
        font-size: 0.9em;
        color: #666;
    }

    .oauth-debugger-tokens-list {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .oauth-debugger-token-item {
        background: #f8f9fa;
        border: 1px solid #ddd;
        border-radius: 6px;
        padding: 15px;
        transition: all 0.3s ease;
    }

    .oauth-debugger-token-item:hover {
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        transform: translateY(-1px);
    }

    .oauth-debugger-token-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }

    .oauth-debugger-token-user,
    .oauth-debugger-token-client {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .oauth-debugger-token-scopes {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        margin-bottom: 10px;
    }

    .oauth-debugger-token-details {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.9em;
    }

    .oauth-debugger-token-time {
        display: flex;
        gap: 15px;
    }

    .oauth-debugger-token-time span {
        display: flex;
        align-items: center;
        gap: 5px;
        color: #666;
    }

    .oauth-debugger-empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
        padding: 40px 20px;
        text-align: center;
        color: #666;
    }

    .oauth-debugger-empty-state .dashicons {
        font-size: 48px;
        width: 48px;
        height: 48px;
        color: #ddd;
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

    .oauth-debugger-badge.debug {
        background: #e2e3e5;
        color: #383d41;
    }

    .oauth-debugger-badge.info {
        background: #cce5ff;
        color: #004085;
    }

    .oauth-debugger-instructions {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .oauth-debugger-code-block {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 6px;
    }

    .oauth-debugger-code-block h3 {
        margin: 0 0 10px 0;
        font-size: 1.1em;
    }

    .oauth-debugger-code-block pre {
        margin: 0;
        padding: 15px;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 4px;
        overflow-x: auto;
    }

    .oauth-debugger-log-levels h3 {
        margin: 0 0 15px 0;
        font-size: 1.1em;
    }

    .oauth-debugger-log-level-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
    }

    .oauth-debugger-log-level-item {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 6px;
        border: 1px solid #ddd;
    }

    .oauth-debugger-log-level-item p {
        margin: 10px 0 0 0;
        font-size: 0.9em;
        color: #666;
    }

    .oauth-debugger-delete-token {
        color: #dc3545;
        border-color: #dc3545;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .oauth-debugger-delete-token:hover {
        background: #dc3545;
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

        .oauth-debugger-token-details {
            flex-direction: column;
            gap: 10px;
            align-items: flex-start;
        }

        .oauth-debugger-token-time {
            flex-direction: column;
            gap: 5px;
        }
    }
</style>

<script>
    jQuery(document).ready(function($) {
        $('.oauth-debugger-delete-token').on('click', function(e) {
            e.preventDefault();

            if (!confirm(oauthDebug.i18n.confirmDeleteToken)) {
                return;
            }

            const button = $(this);
            const tokenId = button.data('token-id');
            const nonce = button.data('nonce');

            button.prop('disabled', true);

            $.post(oauthDebug.ajaxurl, {
                action: 'oauth_debugger_delete_token',
                token_id: tokenId,
                nonce: nonce
            }, function(response) {
                if (response.success) {
                    button.closest('tr').fadeOut(400, function() {
                        $(this).remove();
                        if ($('.oauth-debugger-card table tbody tr').length === 0) {
                            $('.oauth-debugger-card table').replaceWith(
                                '<p class="oauth-debugger-notice">' +
                                '<?php _e('No active tokens found.', 'wp-oauth-debugger'); ?>' +
                                '</p>'
                            );
                        }
                    });
                } else {
                    alert(response.data || oauthDebug.i18n.error);
                }
            }).fail(function() {
                alert(oauthDebug.i18n.error);
            }).always(function() {
                button.prop('disabled', false);
            });
        });
    });
</script>
