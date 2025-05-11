(function ($) {
    'use strict';

    // OAuth Debugger Public
    const OAuthDebuggerPublic = {
        // Configuration
        config: {
            isCollapsed: false,
            isDragging: false,
            dragStartX: 0,
            dragStartY: 0,
            elementStartX: 0,
            elementStartY: 0
        },

        // Initialize
        init: function () {
            this.createDebugPanel();
            this.bindEvents();
            this.loadDebugInfo();
        },

        // Create debug panel
        createDebugPanel: function () {
            const panel = `
                <div class="oauth-debugger-public">
                    <div class="oauth-debugger-public-header">
                        <h3 class="oauth-debugger-public-title">OAuth Debug Info</h3>
                        <button class="oauth-debugger-public-toggle" title="Toggle Debug Panel">−</button>
                    </div>
                    <div class="oauth-debugger-public-content">
                        <div class="oauth-debugger-public-loading">
                            Loading debug information...
                        </div>
                    </div>
                </div>
            `;

            $('body').append(panel);
        },

        // Bind events
        bindEvents: function () {
            const $panel = $('.oauth-debugger-public');
            const $header = $panel.find('.oauth-debugger-public-header');
            const $toggle = $panel.find('.oauth-debugger-public-toggle');

            // Toggle panel
            $toggle.on('click', this.togglePanel.bind(this));

            // Make panel draggable
            $header.on('mousedown', this.startDrag.bind(this));
            $(document).on('mousemove', this.drag.bind(this));
            $(document).on('mouseup', this.stopDrag.bind(this));

            // Touch events for mobile
            $header.on('touchstart', this.startDragTouch.bind(this));
            $(document).on('touchmove', this.dragTouch.bind(this));
            $(document).on('touchend', this.stopDrag.bind(this));
        },

        // Load debug information
        loadDebugInfo: function () {
            const $content = $('.oauth-debugger-public-content');

            $.ajax({
                url: oauthDebuggerPublic.ajaxurl,
                type: 'POST',
                data: {
                    action: 'oauth_debugger_get_public_info',
                    nonce: oauthDebuggerPublic.nonce
                },
                success: function (response) {
                    if (response.success) {
                        OAuthDebuggerPublic.renderDebugInfo(response.data);
                    } else {
                        $content.html('<div class="oauth-debugger-public-error">' +
                            (response.data.message || 'Failed to load debug information') +
                            '</div>');
                    }
                },
                error: function () {
                    $content.html('<div class="oauth-debugger-public-error">' +
                        'Failed to load debug information' +
                        '</div>');
                }
            });
        },

        // Render debug information
        renderDebugInfo: function (data) {
            const $content = $('.oauth-debugger-public-content');
            let html = '';

            // Token Information
            if (data.token) {
                html += this.createSection('Token Information', [
                    { label: 'Access Token', value: this.maskToken(data.token.access_token) },
                    { label: 'Token Type', value: data.token.token_type },
                    { label: 'Expires In', value: data.token.expires_in + ' seconds' },
                    { label: 'Scope', value: data.token.scope || 'none' }
                ]);
            }

            // Client Information
            if (data.client) {
                html += this.createSection('Client Information', [
                    { label: 'Client ID', value: data.client.client_id },
                    { label: 'Client Name', value: data.client.client_name },
                    { label: 'Redirect URI', value: data.client.redirect_uri }
                ]);
            }

            // User Information
            if (data.user) {
                html += this.createSection('User Information', [
                    { label: 'User ID', value: data.user.ID },
                    { label: 'Username', value: data.user.user_login },
                    { label: 'Email', value: data.user.user_email }
                ]);
            }

            // Request Information
            html += this.createSection('Request Information', [
                { label: 'Method', value: data.request.method },
                { label: 'Endpoint', value: data.request.endpoint },
                { label: 'IP Address', value: data.request.ip },
                { label: 'User Agent', value: data.request.user_agent }
            ]);

            // Security Status
            if (data.security) {
                const securityItems = [
                    {
                        label: 'SSL',
                        value: data.security.ssl_enabled ? 'Enabled' : 'Disabled',
                        badge: data.security.ssl_enabled ? 'success' : 'error'
                    }
                ];

                if (data.security.headers) {
                    Object.entries(data.security.headers).forEach(([header, enabled]) => {
                        securityItems.push({
                            label: header,
                            value: enabled ? 'Enabled' : 'Disabled',
                            badge: enabled ? 'success' : 'error'
                        });
                    });
                }

                html += this.createSection('Security Status', securityItems);
            }

            $content.html(html || '<div class="oauth-debugger-public-notice">No debug information available</div>');
        },

        // Create section
        createSection: function (title, items) {
            const itemsHtml = items.map(item => `
                <li class="oauth-debugger-public-item">
                    <span class="oauth-debugger-public-label">${item.label}</span>
                    ${item.badge ?
                    `<span class="oauth-debugger-public-badge ${item.badge}">${item.value}</span>` :
                    `<span class="oauth-debugger-public-value">${item.value}</span>`
                }
                </li>
            `).join('');

            return `
                <div class="oauth-debugger-public-section">
                    <h4>${title}</h4>
                    <ul class="oauth-debugger-public-list">
                        ${itemsHtml}
                    </ul>
                </div>
            `;
        },

        // Mask token
        maskToken: function (token) {
            if (!token) return '';
            if (token.length <= 8) return token;
            return token.substring(0, 4) + '...' + token.substring(token.length - 4);
        },

        // Toggle panel
        togglePanel: function () {
            const $panel = $('.oauth-debugger-public');
            const $toggle = $panel.find('.oauth-debugger-public-toggle');

            this.config.isCollapsed = !this.config.isCollapsed;
            $panel.toggleClass('oauth-debugger-public-collapsed');
            $toggle.text(this.config.isCollapsed ? '+' : '−');
        },

        // Start drag
        startDrag: function (e) {
            if (e.target.classList.contains('oauth-debugger-public-toggle')) {
                return;
            }

            const $panel = $('.oauth-debugger-public');
            const offset = $panel.offset();

            this.config.isDragging = true;
            this.config.dragStartX = e.clientX;
            this.config.dragStartY = e.clientY;
            this.config.elementStartX = offset.left;
            this.config.elementStartY = offset.top;

            $panel.css('transition', 'none');
        },

        // Start drag (touch)
        startDragTouch: function (e) {
            if (e.target.classList.contains('oauth-debugger-public-toggle')) {
                return;
            }

            const touch = e.originalEvent.touches[0];
            const $panel = $('.oauth-debugger-public');
            const offset = $panel.offset();

            this.config.isDragging = true;
            this.config.dragStartX = touch.clientX;
            this.config.dragStartY = touch.clientY;
            this.config.elementStartX = offset.left;
            this.config.elementStartY = offset.top;

            $panel.css('transition', 'none');
            e.preventDefault();
        },

        // Drag
        drag: function (e) {
            if (!this.config.isDragging) return;

            const $panel = $('.oauth-debugger-public');
            const deltaX = e.clientX - this.config.dragStartX;
            const deltaY = e.clientY - this.config.dragStartY;

            $panel.css({
                left: this.config.elementStartX + deltaX,
                top: this.config.elementStartY + deltaY,
                right: 'auto'
            });
        },

        // Drag (touch)
        dragTouch: function (e) {
            if (!this.config.isDragging) return;

            const touch = e.originalEvent.touches[0];
            const $panel = $('.oauth-debugger-public');
            const deltaX = touch.clientX - this.config.dragStartX;
            const deltaY = touch.clientY - this.config.dragStartY;

            $panel.css({
                left: this.config.elementStartX + deltaX,
                top: this.config.elementStartY + deltaY,
                right: 'auto'
            });

            e.preventDefault();
        },

        // Stop drag
        stopDrag: function () {
            if (!this.config.isDragging) return;

            const $panel = $('.oauth-debugger-public');
            this.config.isDragging = false;
            $panel.css('transition', '');

            // Ensure panel stays within viewport
            const offset = $panel.offset();
            const windowWidth = $(window).width();
            const windowHeight = $(window).height();
            const panelWidth = $panel.outerWidth();
            const panelHeight = $panel.outerHeight();

            let left = offset.left;
            let top = offset.top;

            if (left < 0) left = 0;
            if (top < 0) top = 0;
            if (left + panelWidth > windowWidth) left = windowWidth - panelWidth;
            if (top + panelHeight > windowHeight) top = windowHeight - panelHeight;

            $panel.css({
                left: left,
                top: top
            });
        }
    };

    // Initialize when document is ready
    $(document).ready(function () {
        OAuthDebuggerPublic.init();
    });

})(jQuery); 