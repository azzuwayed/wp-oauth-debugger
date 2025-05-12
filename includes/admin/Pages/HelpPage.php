<?php

namespace WP_OAuth_Debugger\Admin\Pages;

/**
 * Help and documentation page
 */
class HelpPage extends BasePage {
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
        return __('OAuth Debugger Help & Documentation', 'wp-oauth-debugger');
    }

    /**
     * Get the page icon
     *
     * @return string
     */
    protected function get_page_icon() {
        return 'editor-help';
    }

    /**
     * Render header actions
     */
    protected function render_header_actions() {
?>
        <a href="https://github.com/azzuwayed/wp-oauth-debugger" target="_blank" class="button button-secondary">
            <span class="dashicons dashicons-github"></span>
            <?php _e('GitHub', 'wp-oauth-debugger'); ?>
        </a>
        <a href="https://github.com/azzuwayed/wp-oauth-debugger/issues/new" target="_blank" class="button button-secondary">
            <span class="dashicons dashicons-warning"></span>
            <?php _e('Report Issue', 'wp-oauth-debugger'); ?>
        </a>
    <?php
    }

    /**
     * Render the main content
     */
    private function render_content() {
    ?>
        <div class="oauth-debugger-help-wrapper">
            <div class="oauth-debugger-help-navigation">
                <div class="oauth-debugger-help-navigation-inner">
                    <div class="oauth-debugger-help-search">
                        <input type="text" id="oauth-debugger-help-search" placeholder="<?php esc_attr_e('Search documentation...', 'wp-oauth-debugger'); ?>">
                    </div>
                    <nav class="oauth-debugger-help-nav">
                        <ul>
                            <li class="active"><a href="#getting-started"><?php _e('Getting Started', 'wp-oauth-debugger'); ?></a></li>
                            <li><a href="#oauth-basics"><?php _e('OAuth Basics', 'wp-oauth-debugger'); ?></a></li>
                            <li><a href="#debugging-tools"><?php _e('Debugging Tools', 'wp-oauth-debugger'); ?></a></li>
                            <li><a href="#security-analysis"><?php _e('Security Analysis', 'wp-oauth-debugger'); ?></a></li>
                            <li><a href="#live-monitoring"><?php _e('Live Monitoring', 'wp-oauth-debugger'); ?></a></li>
                            <li><a href="#faq"><?php _e('FAQ', 'wp-oauth-debugger'); ?></a></li>
                            <li><a href="#changelog"><?php _e('Changelog', 'wp-oauth-debugger'); ?></a></li>
                        </ul>
                    </nav>
                </div>
            </div>

            <div class="oauth-debugger-help-content">
                <section id="getting-started" class="oauth-debugger-help-section">
                    <h2><?php _e('Getting Started', 'wp-oauth-debugger'); ?></h2>
                    <p><?php _e('Welcome to the OAuth Debugger plugin for WordPress! This plugin helps you debug, monitor, and secure your OAuth implementation.', 'wp-oauth-debugger'); ?></p>

                    <h3><?php _e('Plugin Overview', 'wp-oauth-debugger'); ?></h3>
                    <p><?php _e('The OAuth Debugger provides the following features:', 'wp-oauth-debugger'); ?></p>
                    <ul>
                        <li><?php _e('Real-time monitoring of OAuth requests and responses', 'wp-oauth-debugger'); ?></li>
                        <li><?php _e('Security analysis of your OAuth implementation', 'wp-oauth-debugger'); ?></li>
                        <li><?php _e('Token management and inspection', 'wp-oauth-debugger'); ?></li>
                        <li><?php _e('Detailed logs and timeline of OAuth events', 'wp-oauth-debugger'); ?></li>
                        <li><?php _e('Best practice recommendations', 'wp-oauth-debugger'); ?></li>
                    </ul>

                    <h3><?php _e('Quick Start', 'wp-oauth-debugger'); ?></h3>
                    <ol>
                        <li><?php _e('Configure your OAuth settings in the Settings tab', 'wp-oauth-debugger'); ?></li>
                        <li><?php _e('Use the Live Monitor to view OAuth traffic in real-time', 'wp-oauth-debugger'); ?></li>
                        <li><?php _e('Run a security scan to identify potential vulnerabilities', 'wp-oauth-debugger'); ?></li>
                        <li><?php _e('Review the dashboard for an overview of OAuth activity', 'wp-oauth-debugger'); ?></li>
                    </ol>
                </section>

                <section id="oauth-basics" class="oauth-debugger-help-section">
                    <h2><?php _e('OAuth Basics', 'wp-oauth-debugger'); ?></h2>
                    <p><?php _e('OAuth is an open standard for access delegation, commonly used as a way for users to grant websites or applications access to their information on other websites without giving them passwords.', 'wp-oauth-debugger'); ?></p>

                    <h3><?php _e('OAuth 2.0 Flow Types', 'wp-oauth-debugger'); ?></h3>
                    <ul>
                        <li>
                            <strong><?php _e('Authorization Code', 'wp-oauth-debugger'); ?></strong>
                            <p><?php _e('Used by web applications that have a server-side component. The most secure flow, involving a front-channel request and a back-channel request.', 'wp-oauth-debugger'); ?></p>
                        </li>
                        <li>
                            <strong><?php _e('Implicit', 'wp-oauth-debugger'); ?></strong>
                            <p><?php _e('Used by single-page applications (SPAs) that cannot keep a client secret. Less secure than Authorization Code flow.', 'wp-oauth-debugger'); ?></p>
                        </li>
                        <li>
                            <strong><?php _e('Resource Owner Password Credentials', 'wp-oauth-debugger'); ?></strong>
                            <p><?php _e('Used when the application is highly trusted. The user provides their username and password directly to the application.', 'wp-oauth-debugger'); ?></p>
                        </li>
                        <li>
                            <strong><?php _e('Client Credentials', 'wp-oauth-debugger'); ?></strong>
                            <p><?php _e('Used for server-to-server authentication where no user is involved. The application authenticates with its own credentials.', 'wp-oauth-debugger'); ?></p>
                        </li>
                    </ul>

                    <h3><?php _e('Key Concepts', 'wp-oauth-debugger'); ?></h3>
                    <ul>
                        <li>
                            <strong><?php _e('Access Token', 'wp-oauth-debugger'); ?></strong>
                            <p><?php _e('A credential used by a client to access protected resources.', 'wp-oauth-debugger'); ?></p>
                        </li>
                        <li>
                            <strong><?php _e('Refresh Token', 'wp-oauth-debugger'); ?></strong>
                            <p><?php _e('A credential used to obtain new access tokens when the current access token expires.', 'wp-oauth-debugger'); ?></p>
                        </li>
                        <li>
                            <strong><?php _e('Authorization Code', 'wp-oauth-debugger'); ?></strong>
                            <p><?php _e('A short-lived token used to obtain access tokens in the Authorization Code flow.', 'wp-oauth-debugger'); ?></p>
                        </li>
                        <li>
                            <strong><?php _e('Scopes', 'wp-oauth-debugger'); ?></strong>
                            <p><?php _e('Specify what access privileges are being requested by the client.', 'wp-oauth-debugger'); ?></p>
                        </li>
                    </ul>
                </section>

                <section id="debugging-tools" class="oauth-debugger-help-section">
                    <h2><?php _e('Debugging Tools', 'wp-oauth-debugger'); ?></h2>
                    <p><?php _e('The OAuth Debugger provides several tools to help you troubleshoot your OAuth implementation.', 'wp-oauth-debugger'); ?></p>

                    <h3><?php _e('Log Viewer', 'wp-oauth-debugger'); ?></h3>
                    <p><?php _e('The log viewer displays detailed information about OAuth requests and responses, including:', 'wp-oauth-debugger'); ?></p>
                    <ul>
                        <li><?php _e('Request parameters and headers', 'wp-oauth-debugger'); ?></li>
                        <li><?php _e('Response status codes and bodies', 'wp-oauth-debugger'); ?></li>
                        <li><?php _e('Error messages and exceptions', 'wp-oauth-debugger'); ?></li>
                        <li><?php _e('Token issuance and validation events', 'wp-oauth-debugger'); ?></li>
                    </ul>
                    <p><?php _e('You can filter logs by level, date, and client to focus on specific issues.', 'wp-oauth-debugger'); ?></p>

                    <h3><?php _e('Token Inspector', 'wp-oauth-debugger'); ?></h3>
                    <p><?php _e('The token inspector allows you to view detailed information about OAuth tokens, including:', 'wp-oauth-debugger'); ?></p>
                    <ul>
                        <li><?php _e('Token metadata (creation date, expiration, client)', 'wp-oauth-debugger'); ?></li>
                        <li><?php _e('Granted scopes', 'wp-oauth-debugger'); ?></li>
                        <li><?php _e('Token usage history', 'wp-oauth-debugger'); ?></li>
                    </ul>
                    <p><?php _e('You can also revoke tokens, which can be useful when testing revocation endpoints or during security incidents.', 'wp-oauth-debugger'); ?></p>
                </section>

                <section id="security-analysis" class="oauth-debugger-help-section">
                    <h2><?php _e('Security Analysis', 'wp-oauth-debugger'); ?></h2>
                    <p><?php _e('The security analyzer checks your OAuth implementation against best practices and identifies potential vulnerabilities.', 'wp-oauth-debugger'); ?></p>

                    <h3><?php _e('Security Checks', 'wp-oauth-debugger'); ?></h3>
                    <p><?php _e('The analyzer performs the following checks:', 'wp-oauth-debugger'); ?></p>
                    <ul>
                        <li><?php _e('HTTPS usage for all OAuth endpoints', 'wp-oauth-debugger'); ?></li>
                        <li><?php _e('Proper redirect URI validation', 'wp-oauth-debugger'); ?></li>
                        <li><?php _e('State parameter usage', 'wp-oauth-debugger'); ?></li>
                        <li><?php _e('PKCE implementation for public clients', 'wp-oauth-debugger'); ?></li>
                        <li><?php _e('Token expiration policies', 'wp-oauth-debugger'); ?></li>
                        <li><?php _e('Scope validation', 'wp-oauth-debugger'); ?></li>
                        <li><?php _e('Rate limiting and brute force protection', 'wp-oauth-debugger'); ?></li>
                    </ul>

                    <h3><?php _e('Remediation Guidance', 'wp-oauth-debugger'); ?></h3>
                    <p><?php _e('For each identified issue, the analyzer provides:', 'wp-oauth-debugger'); ?></p>
                    <ul>
                        <li><?php _e('Severity rating (high, medium, low)', 'wp-oauth-debugger'); ?></li>
                        <li><?php _e('Detailed description of the vulnerability', 'wp-oauth-debugger'); ?></li>
                        <li><?php _e('Specific remediation steps', 'wp-oauth-debugger'); ?></li>
                        <li><?php _e('References to relevant OAuth specifications and best practices', 'wp-oauth-debugger'); ?></li>
                    </ul>
                </section>

                <section id="live-monitoring" class="oauth-debugger-help-section">
                    <h2><?php _e('Live Monitoring', 'wp-oauth-debugger'); ?></h2>
                    <p><?php _e('The live monitor provides real-time insight into OAuth traffic on your site.', 'wp-oauth-debugger'); ?></p>

                    <h3><?php _e('Features', 'wp-oauth-debugger'); ?></h3>
                    <ul>
                        <li><?php _e('Real-time log stream of OAuth events', 'wp-oauth-debugger'); ?></li>
                        <li><?php _e('Timeline visualization of OAuth flows', 'wp-oauth-debugger'); ?></li>
                        <li><?php _e('Statistics on request volume, error rates, and response times', 'wp-oauth-debugger'); ?></li>
                        <li><?php _e('Active session tracking', 'wp-oauth-debugger'); ?></li>
                    </ul>

                    <h3><?php _e('Use Cases', 'wp-oauth-debugger'); ?></h3>
                    <ul>
                        <li><?php _e('Debugging integration issues with OAuth clients', 'wp-oauth-debugger'); ?></li>
                        <li><?php _e('Monitoring for unusual activity or abuse', 'wp-oauth-debugger'); ?></li>
                        <li><?php _e('Troubleshooting user-reported authentication problems', 'wp-oauth-debugger'); ?></li>
                        <li><?php _e('Performance monitoring and optimization', 'wp-oauth-debugger'); ?></li>
                    </ul>
                </section>

                <section id="faq" class="oauth-debugger-help-section">
                    <h2><?php _e('Frequently Asked Questions', 'wp-oauth-debugger'); ?></h2>

                    <div class="oauth-debugger-faq-item">
                        <h3><?php _e('Does this plugin affect my OAuth server performance?', 'wp-oauth-debugger'); ?></h3>
                        <div class="oauth-debugger-faq-answer">
                            <p><?php _e('The plugin is designed to have minimal impact on performance. Logging is performed asynchronously where possible, and you can adjust the logging level in settings to reduce overhead in production environments.', 'wp-oauth-debugger'); ?></p>
                        </div>
                    </div>

                    <div class="oauth-debugger-faq-item">
                        <h3><?php _e('Is this plugin compatible with specific OAuth servers?', 'wp-oauth-debugger'); ?></h3>
                        <div class="oauth-debugger-faq-answer">
                            <p><?php _e('The plugin is designed to work with any OAuth 2.0 implementation, including popular WordPress plugins like OAuth2 Server, WP OAuth Server, and NextAuth. It monitors the OAuth protocol at the HTTP level, so it should work with any standards-compliant implementation.', 'wp-oauth-debugger'); ?></p>
                        </div>
                    </div>

                    <div class="oauth-debugger-faq-item">
                        <h3><?php _e('How can I clear sensitive data from logs?', 'wp-oauth-debugger'); ?></h3>
                        <div class="oauth-debugger-faq-answer">
                            <p><?php _e('The plugin automatically redacts sensitive information like client secrets, passwords, and tokens from logs. You can also manually clear logs using the "Clear Logs" button in the Live Monitor or configure automatic log rotation in settings.', 'wp-oauth-debugger'); ?></p>
                        </div>
                    </div>

                    <div class="oauth-debugger-faq-item">
                        <h3><?php _e('Can I use this in production?', 'wp-oauth-debugger'); ?></h3>
                        <div class="oauth-debugger-faq-answer">
                            <p><?php _e('Yes, but we recommend using minimal logging levels in production (Warning or Error). The security scanner and token management features are safe to use in production and can help improve your security posture.', 'wp-oauth-debugger'); ?></p>
                        </div>
                    </div>
                </section>

                <section id="changelog" class="oauth-debugger-help-section">
                    <h2><?php _e('Changelog', 'wp-oauth-debugger'); ?></h2>
                    <div class="oauth-debugger-changelog">
                        <div class="oauth-debugger-version">
                            <h3>Version 1.1.0</h3>
                            <div class="oauth-debugger-version-date"><?php _e('Released: August 15, 2023', 'wp-oauth-debugger'); ?></div>
                            <ul>
                                <li><?php _e('Added security analysis feature', 'wp-oauth-debugger'); ?></li>
                                <li><?php _e('Improved token management', 'wp-oauth-debugger'); ?></li>
                                <li><?php _e('Added real-time monitoring dashboard', 'wp-oauth-debugger'); ?></li>
                                <li><?php _e('Enhanced logging capabilities', 'wp-oauth-debugger'); ?></li>
                                <li><?php _e('Bug fixes and performance improvements', 'wp-oauth-debugger'); ?></li>
                            </ul>
                        </div>
                        <div class="oauth-debugger-version">
                            <h3>Version 1.0.0</h3>
                            <div class="oauth-debugger-version-date"><?php _e('Released: May 10, 2023', 'wp-oauth-debugger'); ?></div>
                            <ul>
                                <li><?php _e('Initial release', 'wp-oauth-debugger'); ?></li>
                                <li><?php _e('Basic OAuth request and response logging', 'wp-oauth-debugger'); ?></li>
                                <li><?php _e('Token inspection capabilities', 'wp-oauth-debugger'); ?></li>
                                <li><?php _e('Simple dashboard with OAuth statistics', 'wp-oauth-debugger'); ?></li>
                            </ul>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <style>
            .oauth-debugger-help-wrapper {
                display: flex;
                margin-top: 20px;
                background-color: #fff;
                border: 1px solid #ddd;
                border-radius: 4px;
                overflow: hidden;
            }

            .oauth-debugger-help-navigation {
                width: 250px;
                background-color: #f8f9fa;
                border-right: 1px solid #ddd;
                flex-shrink: 0;
            }

            .oauth-debugger-help-navigation-inner {
                position: sticky;
                top: 32px;
                max-height: calc(100vh - 100px);
                overflow-y: auto;
                padding-bottom: 20px;
            }

            .oauth-debugger-help-search {
                padding: 15px;
                border-bottom: 1px solid #ddd;
            }

            .oauth-debugger-help-search input {
                width: 100%;
                padding: 8px 10px;
                border: 1px solid #ddd;
                border-radius: 4px;
            }

            .oauth-debugger-help-nav ul {
                margin: 0;
                padding: 0;
                list-style: none;
            }

            .oauth-debugger-help-nav li {
                margin: 0;
                padding: 0;
            }

            .oauth-debugger-help-nav li a {
                display: block;
                padding: 10px 15px;
                color: #444;
                text-decoration: none;
                border-bottom: 1px solid transparent;
                border-left: 3px solid transparent;
            }

            .oauth-debugger-help-nav li a:hover {
                background-color: #f0f0f1;
                color: #2271b1;
            }

            .oauth-debugger-help-nav li.active a {
                background-color: rgba(0, 115, 170, 0.05);
                border-left-color: #2271b1;
                color: #2271b1;
                font-weight: 500;
            }

            .oauth-debugger-help-content {
                flex: 1;
                padding: 30px;
                max-width: calc(100% - 250px);
            }

            .oauth-debugger-help-section {
                margin-bottom: 40px;
                padding-bottom: 40px;
                border-bottom: 1px solid #eee;
            }

            .oauth-debugger-help-section:last-child {
                margin-bottom: 0;
                padding-bottom: 0;
                border-bottom: none;
            }

            .oauth-debugger-help-section h2 {
                margin: 0 0 20px;
                padding-bottom: 10px;
                font-size: 24px;
                border-bottom: 1px solid #eee;
            }

            .oauth-debugger-help-section h3 {
                margin: 25px 0 15px;
                font-size: 18px;
            }

            .oauth-debugger-help-section p {
                margin: 0 0 15px;
                line-height: 1.6;
            }

            .oauth-debugger-help-section ul,
            .oauth-debugger-help-section ol {
                margin: 0 0 20px 20px;
                line-height: 1.6;
            }

            .oauth-debugger-help-section li {
                margin-bottom: 8px;
            }

            .oauth-debugger-help-section li p {
                margin: 5px 0 10px;
            }

            .oauth-debugger-help-section strong {
                font-weight: 600;
                color: #333;
            }

            .oauth-debugger-faq-item {
                margin-bottom: 20px;
                background-color: #f8f9fa;
                border-radius: 4px;
                overflow: hidden;
            }

            .oauth-debugger-faq-item h3 {
                margin: 0;
                padding: 15px;
                font-size: 16px;
                background-color: #f0f0f1;
                cursor: pointer;
                position: relative;
            }

            .oauth-debugger-faq-answer {
                padding: 15px;
                border-top: 1px solid #eee;
            }

            .oauth-debugger-faq-answer p {
                margin: 0 0 10px;
            }

            .oauth-debugger-faq-answer p:last-child {
                margin-bottom: 0;
            }

            .oauth-debugger-changelog {
                max-height: 500px;
                overflow-y: auto;
                padding: 0 10px;
            }

            .oauth-debugger-version {
                margin-bottom: 25px;
                padding-bottom: 25px;
                border-bottom: 1px solid #eee;
            }

            .oauth-debugger-version:last-child {
                margin-bottom: 0;
                padding-bottom: 0;
                border-bottom: none;
            }

            .oauth-debugger-version h3 {
                margin: 0 0 5px;
                font-size: 18px;
            }

            .oauth-debugger-version-date {
                margin-bottom: 15px;
                color: #666;
                font-style: italic;
                font-size: 14px;
            }

            @media screen and (max-width: 782px) {
                .oauth-debugger-help-wrapper {
                    flex-direction: column;
                }

                .oauth-debugger-help-navigation {
                    width: 100%;
                    border-right: none;
                    border-bottom: 1px solid #ddd;
                }

                .oauth-debugger-help-navigation-inner {
                    position: static;
                    max-height: none;
                }

                .oauth-debugger-help-content {
                    max-width: 100%;
                    padding: 20px;
                }
            }
        </style>

        <script>
            // Simple scrollspy functionality
            document.addEventListener('DOMContentLoaded', function() {
                const sections = document.querySelectorAll('.oauth-debugger-help-section');
                const navItems = document.querySelectorAll('.oauth-debugger-help-nav li');

                // Click handler for navigation
                document.querySelector('.oauth-debugger-help-nav').addEventListener('click', function(e) {
                    if (e.target.tagName === 'A') {
                        e.preventDefault();
                        const targetId = e.target.getAttribute('href').substring(1);
                        const targetSection = document.getElementById(targetId);

                        if (targetSection) {
                            targetSection.scrollIntoView({
                                behavior: 'smooth'
                            });
                        }
                    }
                });

                // FAQ toggle functionality
                document.querySelectorAll('.oauth-debugger-faq-item h3').forEach(function(heading) {
                    heading.addEventListener('click', function() {
                        const answer = this.nextElementSibling;
                        const isVisible = answer.style.display !== 'none';

                        if (isVisible) {
                            answer.style.display = 'none';
                        } else {
                            answer.style.display = 'block';
                        }
                    });
                });

                // Search functionality
                const searchInput = document.getElementById('oauth-debugger-help-search');
                searchInput.addEventListener('input', function() {
                    const query = this.value.toLowerCase();

                    sections.forEach(function(section) {
                        const content = section.textContent.toLowerCase();
                        const navItem = document.querySelector(`.oauth-debugger-help-nav a[href="#${section.id}"]`).parentElement;

                        if (query === '' || content.includes(query)) {
                            section.style.display = 'block';
                            navItem.style.display = 'block';
                        } else {
                            section.style.display = 'none';
                            navItem.style.display = 'none';
                        }
                    });
                });
            });
        </script>
<?php
    }
}
