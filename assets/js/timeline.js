(function($) {
    'use strict';

    class OAuthTimeline {
        constructor(containerId) {
            this.container = document.getElementById(containerId);
            this.timeline = null;
            this.chart = null;
            this.data = [];
            this.refreshInterval = null;
            this.init();
        }

        init() {
            this.initTimeline();
            this.initChart();
            this.setupEventListeners();
            this.startAutoRefresh();
        }

        initTimeline() {
            const options = {
                width: '100%',
                height: '400px',
                scale_factor: 1,
                timenav_position: 'top',
                timenav_height: 150,
                timenav_height_percentage: 25,
                timenav_mobile_height_percentage: 40,
                timenav_height_min: 150,
                marker_height_min: 30,
                marker_width_min: 100,
                marker_padding: 5,
                start_at_slide: 0,
                start_at_end: false,
                menubar_height: 0,
                zooming_animation_ratio: 0.5,
                zoom_sequence: [0.5, 1, 2, 3, 5, 8, 13, 21, 34, 55, 89],
                language: 'en',
                theme: 'light'
            };

            this.timeline = new TL.Timeline(this.container, [], options);
        }

        initChart() {
            const ctx = document.getElementById('oauth-debugger-chart').getContext('2d');
            this.chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: oauthDebug.i18n.request,
                        data: [],
                        borderColor: '#2271b1',
                        tension: 0.1
                    }, {
                        label: oauthDebug.i18n.response,
                        data: [],
                        borderColor: '#00a32a',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Response Time (ms)'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Time'
                            }
                        }
                    }
                }
            });
        }

        setupEventListeners() {
            // Handle timeline click events
            this.timeline.on('click', (data) => {
                this.showEventDetails(data);
            });

            // Handle auto-refresh toggle
            $('#oauth-debugger-auto-refresh').on('change', (e) => {
                if (e.target.checked) {
                    this.startAutoRefresh();
                } else {
                    this.stopAutoRefresh();
                }
            });

            // Handle clear logs
            $('.oauth-debugger-clear-logs').on('click', (e) => {
                e.preventDefault();
                this.handleClearLogs($(e.currentTarget));
            });

            // Handle view context
            $(document).on('click', '.oauth-debugger-view-context', (e) => {
                e.preventDefault();
                this.showContextModal($(e.currentTarget).data('context'));
            });

            // Handle modal closing
            $('.oauth-debugger-modal-close, .oauth-debugger-modal').on('click', (e) => {
                if (e.target === e.currentTarget) {
                    $('.oauth-debugger-modal').hide();
                }
            });

            // Handle token deletion
            $(document).on('click', '.oauth-debugger-delete-token', (e) => {
                e.preventDefault();
                this.handleDeleteToken($(e.currentTarget));
            });

            // Handle manual refresh
            $('.oauth-debugger-refresh-timeline').on('click', () => {
                this.fetchNewData();
            });
        }

        startAutoRefresh() {
            this.refreshInterval = setInterval(() => {
                this.fetchNewData();
            }, 5000); // Refresh every 5 seconds
        }

        stopAutoRefresh() {
            if (this.refreshInterval) {
                clearInterval(this.refreshInterval);
            }
        }

        fetchNewData() {
            $.ajax({
                url: oauthDebug.ajaxurl,
                type: 'POST',
                data: {
                    action: 'oauth_debug_get_updates',
                    nonce: oauthDebug.nonce
                },
                success: (response) => {
                    if (response.success && response.data) {
                        this.updateTimeline(response.data);
                        this.updateChart(response.data);
                    }
                }
            });
        }

        updateTimeline(data) {
            const timelineData = this.formatTimelineData(data);
            this.timeline.setData(timelineData);
        }

        updateChart(data) {
            const chartData = this.formatChartData(data);
            this.chart.data.labels = chartData.labels;
            this.chart.data.datasets[0].data = chartData.requestTimes;
            this.chart.data.datasets[1].data = chartData.responseTimes;
            this.chart.update();
        }

        formatTimelineData(data) {
            return {
                title: {
                    text: {
                        headline: oauthDebug.i18n.timelineTitle
                    }
                },
                events: data.map(event => ({
                    start_date: {
                        year: new Date(event.timestamp).getFullYear(),
                        month: new Date(event.timestamp).getMonth() + 1,
                        day: new Date(event.timestamp).getDate(),
                        hour: new Date(event.timestamp).getHours(),
                        minute: new Date(event.timestamp).getMinutes(),
                        second: new Date(event.timestamp).getSeconds()
                    },
                    text: {
                        headline: event.message,
                        text: this.formatEventDetails(event)
                    },
                    group: event.type,
                    display_date: event.timestamp,
                    background: this.getEventColor(event)
                }))
            };
        }

        formatChartData(data) {
            const labels = [];
            const requestTimes = [];
            const responseTimes = [];

            data.forEach(event => {
                if (event.type === 'request' || event.type === 'response') {
                    labels.push(event.timestamp);
                    if (event.type === 'request') {
                        requestTimes.push(event.duration || 0);
                    } else {
                        responseTimes.push(event.duration || 0);
                    }
                }
            });

            return { labels, requestTimes, responseTimes };
        }

        formatEventDetails(event) {
            let details = '';
            if (event.context) {
                details += '<pre>' + JSON.stringify(event.context, null, 2) + '</pre>';
            }
            if (event.duration) {
                details += `<p>Duration: ${event.duration}ms</p>`;
            }
            return details;
        }

        getEventColor(event) {
            const colors = {
                request: '#2271b1',
                response: '#00a32a',
                error: '#d63638'
            };
            return colors[event.type] || '#666';
        }

        showEventDetails(data) {
            const modal = $('#oauth-debugger-event-modal');
            const content = modal.find('.oauth-debugger-modal-body');

            content.html(this.formatEventDetails(data.data));
            modal.show();
        }

        handleClearLogs(button) {
            if (!confirm(oauthDebug.i18n.confirmClearLogs)) {
                return;
            }

            button.prop('disabled', true);

            $.post(oauthDebug.ajaxurl, {
                action: 'oauth_debugger_clear_logs',
                nonce: button.data('nonce')
            })
            .done((response) => {
                if (response.success) {
                    $('.oauth-debugger-logs-container').html(
                        '<p class="oauth-debugger-notice">' + oauthDebug.i18n.noLogs + '</p>'
                    );
                } else {
                    alert(response.data || oauthDebug.i18n.error);
                }
            })
            .fail(() => alert(oauthDebug.i18n.error))
            .always(() => button.prop('disabled', false));
        }

        handleDeleteToken(button) {
            if (!confirm(oauthDebug.i18n.confirmDeleteToken)) {
                return;
            }

            button.prop('disabled', true);

            $.post(oauthDebug.ajaxurl, {
                action: 'oauth_debugger_delete_token',
                token_id: button.data('token-id'),
                nonce: button.data('nonce')
            })
            .done((response) => {
                if (response.success) {
                    button.closest('.oauth-debugger-session').fadeOut(400, () => {
                        button.closest('.oauth-debugger-session').remove();
                        this.updateSessionCount();
                    });
                } else {
                    alert(response.data || oauthDebug.i18n.error);
                }
            })
            .fail(() => alert(oauthDebug.i18n.error))
            .always(() => button.prop('disabled', false));
        }

        updateSessionCount() {
            const remainingSessions = $('.oauth-debugger-session').length;
            $('.oauth-debugger-count').text(remainingSessions + ' ' + oauthDebug.i18n.active);

            if (remainingSessions === 0) {
                $('.oauth-debugger-sessions-list').html(
                    '<p class="oauth-debugger-notice">' + oauthDebug.i18n.noSessions + '</p>'
                );
            }
        }

        showContextModal(context) {
            $('.oauth-debugger-context-json').text(JSON.stringify(context, null, 2));
            $('#oauth-debugger-context-modal').show();
        }
    }

    // Initialize timeline when document is ready
    $(document).ready(() => {
        window.oauthTimeline = new OAuthTimeline('oauth-debugger-timeline');
    });

})(jQuery);
