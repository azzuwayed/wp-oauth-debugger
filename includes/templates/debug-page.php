<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap oauth-debugger">
    <h1><?php _e('OAuth Debugger', 'wp-oauth-debugger'); ?></h1>

    <div class="oauth-debugger-grid">
        <!-- Server Information -->
        <div class="oauth-debugger-card">
            <h2><?php _e('Server Information', 'wp-oauth-debugger'); ?></h2>
            <table class="widefat">
                <tbody>
                    <tr>
                        <th><?php _e('WordPress Version', 'wp-oauth-debugger'); ?></th>
                        <td><?php echo esc_html($server_info['wordpress_version']); ?></td>
                    </tr>
                    <tr>
                        <th><?php _e('PHP Version', 'wp-oauth-debugger'); ?></th>
                        <td><?php echo esc_html($server_info['php_version']); ?></td>
                    </tr>
                    <tr>
                        <th><?php _e('OAuth Server Version', 'wp-oauth-debugger'); ?></th>
                        <td><?php echo esc_html($server_info['oauth_server_version']); ?></td>
                    </tr>
                    <tr>
                        <th><?php _e('Debug Mode', 'wp-oauth-debugger'); ?></th>
                        <td>
                            <span class="oauth-debugger-badge <?php echo $server_info['debug_mode'] ? 'success' : 'warning'; ?>">
                                <?php echo $server_info['debug_mode'] ? __('Enabled', 'wp-oauth-debugger') : __('Disabled', 'wp-oauth-debugger'); ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th><?php _e('SSL Enabled', 'wp-oauth-debugger'); ?></th>
                        <td>
                            <span class="oauth-debugger-badge <?php echo $server_info['ssl_enabled'] ? 'success' : 'error'; ?>">
                                <?php echo $server_info['ssl_enabled'] ? __('Yes', 'wp-oauth-debugger') : __('No', 'wp-oauth-debugger'); ?>
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Security Status -->
        <div class="oauth-debugger-card">
            <h2><?php _e('Security Status', 'wp-oauth-debugger'); ?></h2>
            <table class="widefat">
                <tbody>
                    <tr>
                        <th><?php _e('SSL', 'wp-oauth-debugger'); ?></th>
                        <td>
                            <span class="oauth-debugger-badge <?php echo $security_status['ssl_enabled'] ? 'success' : 'error'; ?>">
                                <?php echo $security_status['ssl_enabled'] ? __('Enabled', 'wp-oauth-debugger') : __('Disabled', 'wp-oauth-debugger'); ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th><?php _e('Secure Cookies', 'wp-oauth-debugger'); ?></th>
                        <td>
                            <span class="oauth-debugger-badge <?php echo $security_status['secure_cookies'] ? 'success' : 'warning'; ?>">
                                <?php echo $security_status['secure_cookies'] ? __('Enabled', 'wp-oauth-debugger') : __('Disabled', 'wp-oauth-debugger'); ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th><?php _e('PKCE Support', 'wp-oauth-debugger'); ?></th>
                        <td>
                            <span class="oauth-debugger-badge <?php echo $security_status['pkce_support'] ? 'success' : 'warning'; ?>">
                                <?php echo $security_status['pkce_support'] ? __('Enabled', 'wp-oauth-debugger') : __('Disabled', 'wp-oauth-debugger'); ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th><?php _e('CORS', 'wp-oauth-debugger'); ?></th>
                        <td>
                            <span class="oauth-debugger-badge <?php echo $security_status['cors_enabled']['enabled'] ? 'success' : 'warning'; ?>">
                                <?php echo $security_status['cors_enabled']['enabled'] ? __('Enabled', 'wp-oauth-debugger') : __('Disabled', 'wp-oauth-debugger'); ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th><?php _e('Rate Limiting', 'wp-oauth-debugger'); ?></th>
                        <td>
                            <span class="oauth-debugger-badge <?php echo $security_status['rate_limiting']['enabled'] ? 'success' : 'warning'; ?>">
                                <?php echo $security_status['rate_limiting']['enabled'] ? __('Enabled', 'wp-oauth-debugger') : __('Disabled', 'wp-oauth-debugger'); ?>
                            </span>
                            <?php if ($security_status['rate_limiting']['enabled']): ?>
                                (<?php echo esc_html($security_status['rate_limiting']['requests_per_minute']); ?> <?php _e('requests/min', 'wp-oauth-debugger'); ?>)
                            <?php endif; ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Active Tokens -->
        <div class="oauth-debugger-card">
            <h2><?php _e('Active Tokens', 'wp-oauth-debugger'); ?></h2>
            <?php if (empty($active_tokens)): ?>
                <p class="oauth-debugger-notice"><?php _e('No active tokens found.', 'wp-oauth-debugger'); ?></p>
            <?php else: ?>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th><?php _e('User', 'wp-oauth-debugger'); ?></th>
                            <th><?php _e('Client ID', 'wp-oauth-debugger'); ?></th>
                            <th><?php _e('Scopes', 'wp-oauth-debugger'); ?></th>
                            <th><?php _e('Created', 'wp-oauth-debugger'); ?></th>
                            <th><?php _e('Expires', 'wp-oauth-debugger'); ?></th>
                            <th><?php _e('Actions', 'wp-oauth-debugger'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($active_tokens as $token): ?>
                            <tr>
                                <td><?php echo esc_html($token['user_login']); ?></td>
                                <td><?php echo esc_html($token['client_id']); ?></td>
                                <td><?php echo esc_html(implode(', ', $token['scopes'])); ?></td>
                                <td><?php echo esc_html($token['created_at']); ?></td>
                                <td><?php echo esc_html($token['expires']); ?></td>
                                <td>
                                    <button class="button oauth-debugger-delete-token" 
                                            data-token-id="<?php echo esc_attr($token['id']); ?>"
                                            data-nonce="<?php echo wp_create_nonce('oauth_debug_nonce'); ?>">
                                        <?php _e('Delete', 'wp-oauth-debugger'); ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Debug Instructions -->
    <div class="oauth-debugger-card">
        <h2><?php _e('Debug Instructions', 'wp-oauth-debugger'); ?></h2>
        <div class="oauth-debugger-instructions">
            <p><?php _e('To enable OAuth debugging, add the following to your wp-config.php file:', 'wp-oauth-debugger'); ?></p>
            <pre>define('OAUTH_DEBUG', true);
define('OAUTH_DEBUG_LOG_LEVEL', 'debug'); // Options: debug, info, warning, error
define('OAUTH_DEBUG_LOG_RETENTION', 7); // Number of days to keep logs</pre>
            
            <p><?php _e('Available log levels:', 'wp-oauth-debugger'); ?></p>
            <ul>
                <li><strong>debug</strong>: <?php _e('Detailed debugging information', 'wp-oauth-debugger'); ?></li>
                <li><strong>info</strong>: <?php _e('General information about OAuth operations', 'wp-oauth-debugger'); ?></li>
                <li><strong>warning</strong>: <?php _e('Warning messages about potential issues', 'wp-oauth-debugger'); ?></li>
                <li><strong>error</strong>: <?php _e('Error messages about failed operations', 'wp-oauth-debugger'); ?></li>
            </ul>
        </div>
    </div>
</div>

<style>
.oauth-debugger-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.oauth-debugger-card {
    background: #fff;
    border: 1px solid #ccd0d4;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
    padding: 20px;
}

.oauth-debugger-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: 600;
}

.oauth-debugger-badge.success {
    background: #dff0d8;
    color: #3c763d;
}

.oauth-debugger-badge.warning {
    background: #fcf8e3;
    color: #8a6d3b;
}

.oauth-debugger-badge.error {
    background: #f2dede;
    color: #a94442;
}

.oauth-debugger-instructions {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 4px;
}

.oauth-debugger-instructions pre {
    background: #fff;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 3px;
    margin: 10px 0;
}

.oauth-debugger-notice {
    padding: 10px;
    background: #f8f9fa;
    border-left: 4px solid #007cba;
    margin: 0;
}

.oauth-debugger-delete-token {
    color: #dc3545;
    border-color: #dc3545;
}

.oauth-debugger-delete-token:hover {
    background: #dc3545;
    color: #fff;
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