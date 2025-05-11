<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap oauth-debugger">
    <h1><?php _e('OAuth Live Monitor', 'wp-oauth-debugger'); ?></h1>

    <div class="oauth-debugger-grid">
        <!-- Live Logs -->
        <div class="oauth-debugger-card oauth-debugger-logs">
            <div class="oauth-debugger-card-header">
                <h2><?php _e('Live Logs', 'wp-oauth-debugger'); ?></h2>
                <div class="oauth-debugger-card-actions">
                    <button class="button oauth-debugger-clear-logs" data-nonce="<?php echo wp_create_nonce('oauth_debug_nonce'); ?>">
                        <?php _e('Clear Logs', 'wp-oauth-debugger'); ?>
                    </button>
                    <label class="oauth-debugger-auto-refresh">
                        <input type="checkbox" id="oauth-debugger-auto-refresh" checked>
                        <?php _e('Auto-refresh', 'wp-oauth-debugger'); ?>
                    </label>
                </div>
            </div>
            <div class="oauth-debugger-logs-container">
                <?php if (empty($recent_logs)): ?>
                    <p class="oauth-debugger-notice"><?php _e('No logs found.', 'wp-oauth-debugger'); ?></p>
                <?php else: ?>
                    <table class="widefat">
                        <thead>
                            <tr>
                                <th><?php _e('Time', 'wp-oauth-debugger'); ?></th>
                                <th><?php _e('Level', 'wp-oauth-debugger'); ?></th>
                                <th><?php _e('Message', 'wp-oauth-debugger'); ?></th>
                                <th><?php _e('Context', 'wp-oauth-debugger'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_logs as $log): ?>
                                <tr class="oauth-debugger-log-level-<?php echo esc_attr($log['level']); ?>">
                                    <td><?php echo esc_html($log['timestamp']); ?></td>
                                    <td>
                                        <span class="oauth-debugger-badge oauth-debugger-level-<?php echo esc_attr($log['level']); ?>">
                                            <?php echo esc_html(ucfirst($log['level'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo esc_html($log['message']); ?></td>
                                    <td>
                                        <button class="button oauth-debugger-view-context" 
                                                data-context='<?php echo esc_attr(json_encode($log['context'])); ?>'>
                                            <?php _e('View', 'wp-oauth-debugger'); ?>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Active Sessions -->
        <div class="oauth-debugger-card oauth-debugger-sessions">
            <div class="oauth-debugger-card-header">
                <h2><?php _e('Active Sessions', 'wp-oauth-debugger'); ?></h2>
                <div class="oauth-debugger-card-actions">
                    <span class="oauth-debugger-count">
                        <?php echo count($active_tokens); ?> <?php _e('active', 'wp-oauth-debugger'); ?>
                    </span>
                </div>
            </div>
            <?php if (empty($active_tokens)): ?>
                <p class="oauth-debugger-notice"><?php _e('No active sessions found.', 'wp-oauth-debugger'); ?></p>
            <?php else: ?>
                <div class="oauth-debugger-sessions-list">
                    <?php foreach ($active_tokens as $token): ?>
                        <div class="oauth-debugger-session">
                            <div class="oauth-debugger-session-header">
                                <strong><?php echo esc_html($token['user_login']); ?></strong>
                                <span class="oauth-debugger-session-client">
                                    <?php echo esc_html($token['client_id']); ?>
                                </span>
                            </div>
                            <div class="oauth-debugger-session-details">
                                <div class="oauth-debugger-session-scopes">
                                    <?php foreach ($token['scopes'] as $scope): ?>
                                        <span class="oauth-debugger-badge"><?php echo esc_html($scope); ?></span>
                                    <?php endforeach; ?>
                                </div>
                                <div class="oauth-debugger-session-time">
                                    <span class="oauth-debugger-session-created">
                                        <?php _e('Created:', 'wp-oauth-debugger'); ?> 
                                        <?php echo esc_html($token['created_at']); ?>
                                    </span>
                                    <span class="oauth-debugger-session-expires">
                                        <?php _e('Expires:', 'wp-oauth-debugger'); ?> 
                                        <?php echo esc_html($token['expires']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="oauth-debugger-session-actions">
                                <button class="button oauth-debugger-delete-token" 
                                        data-token-id="<?php echo esc_attr($token['id']); ?>"
                                        data-nonce="<?php echo wp_create_nonce('oauth_debug_nonce'); ?>">
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

<!-- Context Modal -->
<div id="oauth-debugger-context-modal" class="oauth-debugger-modal" style="display: none;">
    <div class="oauth-debugger-modal-content">
        <div class="oauth-debugger-modal-header">
            <h3><?php _e('Log Context', 'wp-oauth-debugger'); ?></h3>
            <button class="oauth-debugger-modal-close">&times;</button>
        </div>
        <div class="oauth-debugger-modal-body">
            <pre class="oauth-debugger-context-json"></pre>
        </div>
    </div>
</div>

<style>
.oauth-debugger-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
    margin: 20px 0;
}

.oauth-debugger-card {
    background: #fff;
    border: 1px solid #ccd0d4;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.oauth-debugger-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    border-bottom: 1px solid #ccd0d4;
}

.oauth-debugger-card-header h2 {
    margin: 0;
}

.oauth-debugger-card-actions {
    display: flex;
    align-items: center;
    gap: 10px;
}

.oauth-debugger-logs-container {
    max-height: 600px;
    overflow-y: auto;
    padding: 20px;
}

.oauth-debugger-sessions-list {
    max-height: 600px;
    overflow-y: auto;
    padding: 20px;
}

.oauth-debugger-session {
    background: #f8f9fa;
    border: 1px solid #ddd;
    border-radius: 4px;
    margin-bottom: 10px;
    padding: 15px;
}

.oauth-debugger-session-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
}

.oauth-debugger-session-client {
    color: #666;
    font-size: 0.9em;
}

.oauth-debugger-session-details {
    margin-bottom: 10px;
}

.oauth-debugger-session-scopes {
    margin-bottom: 5px;
}

.oauth-debugger-session-time {
    font-size: 0.9em;
    color: #666;
}

.oauth-debugger-session-time span {
    margin-right: 15px;
}

.oauth-debugger-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: 600;
    margin-right: 5px;
}

.oauth-debugger-level-debug {
    background: #e3f2fd;
    color: #1976d2;
}

.oauth-debugger-level-info {
    background: #e8f5e9;
    color: #2e7d32;
}

.oauth-debugger-level-warning {
    background: #fff3e0;
    color: #f57c00;
}

.oauth-debugger-level-error {
    background: #ffebee;
    color: #c62828;
}

.oauth-debugger-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 100000;
}

.oauth-debugger-modal-content {
    position: relative;
    background: #fff;
    margin: 10% auto;
    padding: 0;
    width: 70%;
    max-width: 800px;
    border-radius: 4px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.oauth-debugger-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    border-bottom: 1px solid #ddd;
}

.oauth-debugger-modal-header h3 {
    margin: 0;
}

.oauth-debugger-modal-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #666;
}

.oauth-debugger-modal-body {
    padding: 20px;
    max-height: 70vh;
    overflow-y: auto;
}

.oauth-debugger-context-json {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 4px;
    margin: 0;
    white-space: pre-wrap;
    word-wrap: break-word;
}

.oauth-debugger-auto-refresh {
    display: flex;
    align-items: center;
    gap: 5px;
    margin-left: 10px;
}

.oauth-debugger-count {
    font-size: 0.9em;
    color: #666;
}
</style>

<script>
jQuery(document).ready(function($) {
    let autoRefreshInterval;
    const refreshInterval = 5000; // 5 seconds

    function startAutoRefresh() {
        if (autoRefreshInterval) {
            clearInterval(autoRefreshInterval);
        }
        autoRefreshInterval = setInterval(refreshData, refreshInterval);
    }

    function stopAutoRefresh() {
        if (autoRefreshInterval) {
            clearInterval(autoRefreshInterval);
            autoRefreshInterval = null;
        }
    }

    function refreshData() {
        $.post(oauthDebug.ajaxurl, {
            action: 'oauth_debugger_get_updates',
            nonce: oauthDebug.nonce
        }, function(response) {
            if (response.success) {
                updateLogs(response.data.logs);
                updateSessions(response.data.tokens);
            }
        });
    }

    function updateLogs(logs) {
        const container = $('.oauth-debugger-logs-container');
        if (logs.length === 0) {
            container.html('<p class="oauth-debugger-notice"><?php _e('No logs found.', 'wp-oauth-debugger'); ?></p>');
            return;
        }

        const table = $('<table class="widefat"></table>');
        const thead = $('<thead><tr><th><?php _e('Time', 'wp-oauth-debugger'); ?></th><th><?php _e('Level', 'wp-oauth-debugger'); ?></th><th><?php _e('Message', 'wp-oauth-debugger'); ?></th><th><?php _e('Context', 'wp-oauth-debugger'); ?></th></tr></thead>');
        const tbody = $('<tbody></tbody>');

        logs.forEach(function(log) {
            const row = $('<tr class="oauth-debugger-log-level-' + log.level + '"></tr>');
            row.append('<td>' + log.timestamp + '</td>');
            row.append('<td><span class="oauth-debugger-badge oauth-debugger-level-' + log.level + '">' + 
                      log.level.charAt(0).toUpperCase() + log.level.slice(1) + '</span></td>');
            row.append('<td>' + log.message + '</td>');
            row.append('<td><button class="button oauth-debugger-view-context" data-context=\'' + 
                      JSON.stringify(log.context) + '\'><?php _e('View', 'wp-oauth-debugger'); ?></button></td>');
            tbody.append(row);
        });

        table.append(thead).append(tbody);
        container.html(table);
    }

    function updateSessions(tokens) {
        const container = $('.oauth-debugger-sessions-list');
        if (tokens.length === 0) {
            container.html('<p class="oauth-debugger-notice"><?php _e('No active sessions found.', 'wp-oauth-debugger'); ?></p>');
            $('.oauth-debugger-count').text('0 <?php _e('active', 'wp-oauth-debugger'); ?>');
            return;
        }

        $('.oauth-debugger-count').text(tokens.length + ' <?php _e('active', 'wp-oauth-debugger'); ?>');
        
        const sessions = $('<div class="oauth-debugger-sessions-list"></div>');
        tokens.forEach(function(token) {
            const session = $('<div class="oauth-debugger-session"></div>');
            session.append('<div class="oauth-debugger-session-header"><strong>' + token.user_login + 
                         '</strong><span class="oauth-debugger-session-client">' + token.client_id + '</span></div>');
            
            const scopes = $('<div class="oauth-debugger-session-scopes"></div>');
            token.scopes.forEach(function(scope) {
                scopes.append('<span class="oauth-debugger-badge">' + scope + '</span>');
            });
            session.append(scopes);

            session.append('<div class="oauth-debugger-session-time"><span class="oauth-debugger-session-created">' +
                         '<?php _e('Created:', 'wp-oauth-debugger'); ?> ' + token.created_at + '</span>' +
                         '<span class="oauth-debugger-session-expires"><?php _e('Expires:', 'wp-oauth-debugger'); ?> ' + 
                         token.expires + '</span></div>');

            session.append('<div class="oauth-debugger-session-actions"><button class="button oauth-debugger-delete-token" ' +
                         'data-token-id="' + token.id + '" data-nonce="' + oauthDebug.nonce + '">' +
                         '<?php _e('Revoke', 'wp-oauth-debugger'); ?></button></div>');

            sessions.append(session);
        });

        container.replaceWith(sessions);
    }

    // Auto-refresh toggle
    $('#oauth-debugger-auto-refresh input').on('change', function() {
        if (this.checked) {
            startAutoRefresh();
        } else {
            stopAutoRefresh();
        }
    });

    // Start auto-refresh if enabled
    if ($('#oauth-debugger-auto-refresh input').is(':checked')) {
        startAutoRefresh();
    }

    // Clear logs
    $('.oauth-debugger-clear-logs').on('click', function() {
        if (!confirm(oauthDebug.i18n.confirmClearLogs)) {
            return;
        }

        const button = $(this);
        button.prop('disabled', true);

        $.post(oauthDebug.ajaxurl, {
            action: 'oauth_debugger_clear_logs',
            nonce: button.data('nonce')
        }, function(response) {
            if (response.success) {
                $('.oauth-debugger-logs-container').html(
                    '<p class="oauth-debugger-notice"><?php _e('No logs found.', 'wp-oauth-debugger'); ?></p>'
                );
            } else {
                alert(response.data || oauthDebug.i18n.error);
            }
        }).fail(function() {
            alert(oauthDebug.i18n.error);
        }).always(function() {
            button.prop('disabled', false);
        });
    });

    // View context
    $(document).on('click', '.oauth-debugger-view-context', function() {
        const context = $(this).data('context');
        $('.oauth-debugger-context-json').text(JSON.stringify(context, null, 2));
        $('#oauth-debugger-context-modal').show();
    });

    // Close modal
    $('.oauth-debugger-modal-close, #oauth-debugger-context-modal').on('click', function(e) {
        if (e.target === this) {
            $('#oauth-debugger-context-modal').hide();
        }
    });

    // Delete token
    $(document).on('click', '.oauth-debugger-delete-token', function(e) {
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
                button.closest('.oauth-debugger-session').fadeOut(400, function() {
                    $(this).remove();
                    const remainingSessions = $('.oauth-debugger-session').length;
                    $('.oauth-debugger-count').text(remainingSessions + ' <?php _e('active', 'wp-oauth-debugger'); ?>');
                    if (remainingSessions === 0) {
                        $('.oauth-debugger-sessions-list').html(
                            '<p class="oauth-debugger-notice"><?php _e('No active sessions found.', 'wp-oauth-debugger'); ?></p>'
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