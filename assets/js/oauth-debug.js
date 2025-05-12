
/**
 * OAuth Debugger Admin JavaScript
 */
(function($) {
    'use strict';

    /**
     * Main OAuth Debugger object
     */
    const OAuthDebugger = {
        /**
         * Initialize the app
         */
        init: function() {
            this.initEventListeners();
            this.initTooltips();
            this.initTabs();
        },

        /**
         * Initialize event listeners
         */
        initEventListeners: function() {
            // Clear logs
            $('.oauth-debugger-clear-logs').on('click', function(e) {
                e.preventDefault();
                if (confirm(oauthDebug.i18n.confirmClearLogs)) {
                    OAuthDebugger.clearLogs();
                }
            });

            // Delete token
            $('.oauth-debugger-delete-token').on('click', function(e) {
                e.preventDefault();
                const tokenId = $(this).data('token-id');
                if (confirm(oauthDebug.i18n.confirmDeleteToken)) {
                    OAuthDebugger.deleteToken(tokenId);
                }
            });

            // View token details
            $('.oauth-debugger-view-token').on('click', function(e) {
                e.preventDefault();
                const tokenId = $(this).data('token-id');
                OAuthDebugger.viewTokenDetails(tokenId);
            });

            // Auto-refresh toggle
            $('#oauth-debugger-auto-refresh').on('change', function() {
                OAuthDebugger.toggleAutoRefresh($(this).is(':checked'));
            });

            // Refresh button
            $('.oauth-debugger-refresh').on('click', function(e) {
                e.preventDefault();
                OAuthDebugger.refreshData();
            });

            // Database setup button
            $('.oauth-debugger-setup-database').on('click', function(e) {
                e.preventDefault();
                if (confirm(oauthDebug.i18n.confirmDatabaseSetup || 'Are you sure you want to set up the database?')) {
                    OAuthDebugger.setupDatabase();
                }
            });

            // Empty database button
            $('.oauth-debugger-empty-database').on('click', function(e) {
                e.preventDefault();
                if (confirm(oauthDebug.i18n.confirmEmptyDatabase || 'Are you sure you want to empty the database? This will remove all logs and data.')) {
                    OAuthDebugger.emptyDatabase();
                }
            });

            // Remove database button
            $('.oauth-debugger-remove-database').on('click', function(e) {
                e.preventDefault();
                if (confirm(oauthDebug.i18n.confirmRemoveDatabase || 'Are you sure you want to remove the database tables? This cannot be undone.')) {
                    OAuthDebugger.removeDatabase();
                }
            });

            // Reset plugin button
            $('.oauth-debugger-reset-plugin').on('click', function(e) {
                e.preventDefault();
                if (confirm(oauthDebug.i18n.confirmResetPlugin || 'Are you sure you want to reset all plugin data? This will remove all settings, logs, and database tables. This cannot be undone.')) {
                    OAuthDebugger.resetPlugin();
                }
            });

            console.log('OAuth Debugger: Event listeners initialized');
        },

        /**
         * Initialize tooltips
         */
        initTooltips: function() {
            if ($.fn.tooltip) {
                $('.oauth-debugger-tooltip').tooltip();
            }
        },

        /**
         * Initialize tabs
         */
        initTabs: function() {
            // Don't interfere with settings page tabs which use server-side navigation
            if (window.location.href.indexOf('page=oauth-debugger-settings') > -1) {
                return;
            }

            $('.oauth-debugger-tabs-nav a').on('click', function(e) {
                // Only handle tab clicks for non-settings pages
                if ($(this).attr('href').indexOf('page=oauth-debugger-settings') > -1) {
                    return; // Let the browser handle the navigation
                }

                e.preventDefault();
                const target = $(this).attr('href');

                // Update active tab
                $('.oauth-debugger-tabs-nav a').removeClass('active');
                $(this).addClass('active');

                // Show target content
                $('.oauth-debugger-tab-content').hide();
                $(target).show();

                // Update URL hash
                window.location.hash = target;
            });

            // Only process hash-based tabs if we're not on the settings page
            if (window.location.href.indexOf('page=oauth-debugger-settings') === -1) {
                // Check for hash on page load
                if (window.location.hash) {
                    const hash = window.location.hash;
                    $('.oauth-debugger-tabs-nav a[href="' + hash + '"]').trigger('click');
                } else {
                    // Activate first tab by default
                    $('.oauth-debugger-tabs-nav a:first').trigger('click');
                }
            }
        },

        /**
         * Clear all logs
         */
        clearLogs: function() {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'oauth_debugger_clear_logs',
                    nonce: oauthDebug.nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert(oauthDebug.i18n.logsCleared);
                        location.reload();
                    } else {
                        alert(oauthDebug.i18n.error);
                    }
                },
                error: function() {
                    alert(oauthDebug.i18n.error);
                }
            });
        },

        /**
         * Delete a token
         *
         * @param {string} tokenId The token ID to delete
         */
        deleteToken: function(tokenId) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'oauth_debugger_delete_token',
                    nonce: oauthDebug.nonce,
                    token_id: tokenId
                },
                success: function(response) {
                    if (response.success) {
                        alert(oauthDebug.i18n.tokenDeleted);
                        location.reload();
                    } else {
                        alert(oauthDebug.i18n.error);
                    }
                },
                error: function() {
                    alert(oauthDebug.i18n.error);
                }
            });
        },

        /**
         * View token details
         *
         * @param {string} tokenId The token ID to view
         */
        viewTokenDetails: function(tokenId) {
            // Implementation will depend on UI requirements
            console.log('View token details for: ' + tokenId);
        },

        /**
         * Toggle auto-refresh
         *
         * @param {boolean} enabled Whether auto-refresh is enabled
         */
        toggleAutoRefresh: function(enabled) {
            if (this.refreshInterval) {
                clearInterval(this.refreshInterval);
                this.refreshInterval = null;
            }

            if (enabled) {
                this.refreshInterval = setInterval(function() {
                    OAuthDebugger.refreshData();
                }, 30000); // Refresh every 30 seconds
            }
        },

        /**
         * Refresh data
         */
        refreshData: function() {
            location.reload();
        },

        /**
         * Setup database
         */
        setupDatabase: function() {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'oauth_debugger_setup_database',
                    nonce: oauthDebug.nonce
                },
                beforeSend: function() {
                    $('.oauth-debugger-setup-database').prop('disabled', true).text(oauthDebug.i18n.processing || 'Processing...');
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message);
                        location.reload();
                    } else {
                        alert(response.data.message);
                    }
                },
                error: function() {
                    alert(oauthDebug.i18n.error || 'An error occurred.');
                },
                complete: function() {
                    $('.oauth-debugger-setup-database').prop('disabled', false).html('<span class="dashicons dashicons-database-add"></span> ' + (oauthDebug.i18n.setupDatabase || 'Setup Database'));
                }
            });
        },

        /**
         * Empty database
         */
        emptyDatabase: function() {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'oauth_debugger_empty_database',
                    nonce: oauthDebug.nonce
                },
                beforeSend: function() {
                    $('.oauth-debugger-empty-database').prop('disabled', true).text(oauthDebug.i18n.processing || 'Processing...');
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message);
                        location.reload();
                    } else {
                        alert(response.data.message);
                    }
                },
                error: function() {
                    alert(oauthDebug.i18n.error || 'An error occurred.');
                },
                complete: function() {
                    $('.oauth-debugger-empty-database').prop('disabled', false).html('<span class="dashicons dashicons-trash"></span> ' + (oauthDebug.i18n.emptyDatabase || 'Empty Database'));
                }
            });
        },

        /**
         * Remove database
         */
        removeDatabase: function() {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'oauth_debugger_remove_database',
                    nonce: oauthDebug.nonce
                },
                beforeSend: function() {
                    $('.oauth-debugger-remove-database').prop('disabled', true).text(oauthDebug.i18n.processing || 'Processing...');
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message);
                        location.reload();
                    } else {
                        alert(response.data.message);
                    }
                },
                error: function() {
                    alert(oauthDebug.i18n.error || 'An error occurred.');
                },
                complete: function() {
                    $('.oauth-debugger-remove-database').prop('disabled', false).html('<span class="dashicons dashicons-warning"></span> ' + (oauthDebug.i18n.removeDatabase || 'Remove Database Tables'));
                }
            });
        },

        /**
         * Reset plugin
         */
        resetPlugin: function() {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'oauth_debugger_reset_plugin',
                    nonce: oauthDebug.nonce
                },
                beforeSend: function() {
                    $('.oauth-debugger-reset-plugin').prop('disabled', true).text(oauthDebug.i18n.processing || 'Processing...');
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message);
                        location.reload();
                    } else {
                        alert(response.data.message);
                    }
                },
                error: function() {
                    alert(oauthDebug.i18n.error || 'An error occurred.');
                },
                complete: function() {
                    $('.oauth-debugger-reset-plugin').prop('disabled', false).html('<span class="dashicons dashicons-warning"></span> ' + (oauthDebug.i18n.resetPlugin || 'Reset All Plugin Data'));
                }
            });
        }
    };

    // Initialize on DOM ready
    $(function() {
        OAuthDebugger.init();
    });

})(jQuery);
