<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap oauth-debugger">
    <h1><?php _e('OAuth Security Analysis', 'wp-oauth-debugger'); ?></h1>

    <div class="oauth-debugger-grid">
        <!-- Security Overview -->
        <div class="oauth-debugger-card">
            <h2><?php _e('Security Overview', 'wp-oauth-debugger'); ?></h2>
            <div class="oauth-debugger-security-score">
                <?php
                $score = 0;
                $total = 0;
                
                // SSL
                if ($security_status['ssl_enabled']) {
                    $score += 2;
                }
                $total += 2;
                
                // Secure Cookies
                if ($security_status['secure_cookies']) {
                    $score += 1;
                }
                $total += 1;
                
                // PKCE
                if ($security_status['pkce_support']) {
                    $score += 2;
                }
                $total += 2;
                
                // CORS
                if ($security_status['cors_enabled']['enabled']) {
                    $score += 1;
                }
                $total += 1;
                
                // Rate Limiting
                if ($security_status['rate_limiting']['enabled']) {
                    $score += 2;
                }
                $total += 2;
                
                // Security Headers
                $headers_score = array_sum($security_status['security_headers']);
                $headers_total = count($security_status['security_headers']);
                $score += $headers_score;
                $total += $headers_total;
                
                $percentage = round(($score / $total) * 100);
                $score_class = $percentage >= 80 ? 'success' : ($percentage >= 60 ? 'warning' : 'error');
                ?>
                <div class="oauth-debugger-score-circle <?php echo $score_class; ?>">
                    <span class="oauth-debugger-score-value"><?php echo $percentage; ?>%</span>
                    <span class="oauth-debugger-score-label"><?php _e('Security Score', 'wp-oauth-debugger'); ?></span>
                </div>
            </div>
        </div>

        <!-- Security Headers -->
        <div class="oauth-debugger-card">
            <h2><?php _e('Security Headers', 'wp-oauth-debugger'); ?></h2>
            <table class="widefat">
                <tbody>
                    <?php foreach ($security_status['security_headers'] as $header => $enabled): ?>
                        <tr>
                            <th><?php echo esc_html($header); ?></th>
                            <td>
                                <span class="oauth-debugger-badge <?php echo $enabled ? 'success' : 'error'; ?>">
                                    <?php echo $enabled ? __('Enabled', 'wp-oauth-debugger') : __('Disabled', 'wp-oauth-debugger'); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Token Security -->
        <div class="oauth-debugger-card">
            <h2><?php _e('Token Security', 'wp-oauth-debugger'); ?></h2>
            <table class="widefat">
                <tbody>
                    <tr>
                        <th><?php _e('Access Token Lifetime', 'wp-oauth-debugger'); ?></th>
                        <td>
                            <?php 
                            $lifetime = $security_status['token_lifetime']['access_token'];
                            $lifetime_class = $lifetime <= 3600 ? 'success' : ($lifetime <= 7200 ? 'warning' : 'error');
                            ?>
                            <span class="oauth-debugger-badge <?php echo $lifetime_class; ?>">
                                <?php echo esc_html($lifetime); ?> <?php _e('seconds', 'wp-oauth-debugger'); ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th><?php _e('Refresh Token Lifetime', 'wp-oauth-debugger'); ?></th>
                        <td>
                            <?php 
                            $lifetime = $security_status['token_lifetime']['refresh_token'];
                            $lifetime_class = $lifetime <= 1209600 ? 'success' : ($lifetime <= 2592000 ? 'warning' : 'error');
                            ?>
                            <span class="oauth-debugger-badge <?php echo $lifetime_class; ?>">
                                <?php echo esc_html($lifetime); ?> <?php _e('seconds', 'wp-oauth-debugger'); ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th><?php _e('PKCE Support', 'wp-oauth-debugger'); ?></th>
                        <td>
                            <span class="oauth-debugger-badge <?php echo $security_status['pkce_support'] ? 'success' : 'error'; ?>">
                                <?php echo $security_status['pkce_support'] ? __('Enabled', 'wp-oauth-debugger') : __('Disabled', 'wp-oauth-debugger'); ?>
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Rate Limiting -->
        <div class="oauth-debugger-card">
            <h2><?php _e('Rate Limiting', 'wp-oauth-debugger'); ?></h2>
            <table class="widefat">
                <tbody>
                    <tr>
                        <th><?php _e('Status', 'wp-oauth-debugger'); ?></th>
                        <td>
                            <span class="oauth-debugger-badge <?php echo $security_status['rate_limiting']['enabled'] ? 'success' : 'error'; ?>">
                                <?php echo $security_status['rate_limiting']['enabled'] ? __('Enabled', 'wp-oauth-debugger') : __('Disabled', 'wp-oauth-debugger'); ?>
                            </span>
                        </td>
                    </tr>
                    <?php if ($security_status['rate_limiting']['enabled']): ?>
                        <tr>
                            <th><?php _e('Requests per Minute', 'wp-oauth-debugger'); ?></th>
                            <td>
                                <?php 
                                $rpm = $security_status['rate_limiting']['requests_per_minute'];
                                $rpm_class = $rpm <= 60 ? 'success' : ($rpm <= 120 ? 'warning' : 'error');
                                ?>
                                <span class="oauth-debugger-badge <?php echo $rpm_class; ?>">
                                    <?php echo esc_html($rpm); ?> <?php _e('requests/min', 'wp-oauth-debugger'); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Security Recommendations -->
    <div class="oauth-debugger-card">
        <h2><?php _e('Security Recommendations', 'wp-oauth-debugger'); ?></h2>
        <div class="oauth-debugger-recommendations">
            <?php
            $recommendations = array();

            // SSL
            if (!$security_status['ssl_enabled']) {
                $recommendations[] = array(
                    'level' => 'error',
                    'message' => __('Enable SSL/HTTPS for your WordPress site.', 'wp-oauth-debugger'),
                    'details' => __('SSL is essential for secure OAuth communication. Install an SSL certificate and configure WordPress to use HTTPS.', 'wp-oauth-debugger')
                );
            }

            // Secure Cookies
            if (!$security_status['secure_cookies']) {
                $recommendations[] = array(
                    'level' => 'warning',
                    'message' => __('Configure secure cookies.', 'wp-oauth-debugger'),
                    'details' => __('Set COOKIEPATH to "/" in wp-config.php to ensure cookies are only sent over HTTPS.', 'wp-oauth-debugger')
                );
            }

            // PKCE
            if (!$security_status['pkce_support']) {
                $recommendations[] = array(
                    'level' => 'warning',
                    'message' => __('Enable PKCE support.', 'wp-oauth-debugger'),
                    'details' => __('PKCE (Proof Key for Code Exchange) adds an extra layer of security for public clients. Enable it in your OAuth server settings.', 'wp-oauth-debugger')
                );
            }

            // CORS
            if (!$security_status['cors_enabled']['enabled']) {
                $recommendations[] = array(
                    'level' => 'warning',
                    'message' => __('Configure CORS headers.', 'wp-oauth-debugger'),
                    'details' => __('Set appropriate CORS headers to control which domains can access your OAuth endpoints.', 'wp-oauth-debugger')
                );
            }

            // Rate Limiting
            if (!$security_status['rate_limiting']['enabled']) {
                $recommendations[] = array(
                    'level' => 'warning',
                    'message' => __('Enable rate limiting.', 'wp-oauth-debugger'),
                    'details' => __('Implement rate limiting to prevent brute force attacks and abuse of your OAuth endpoints.', 'wp-oauth-debugger')
                );
            }

            // Security Headers
            foreach ($security_status['security_headers'] as $header => $enabled) {
                if (!$enabled) {
                    $recommendations[] = array(
                        'level' => 'warning',
                        'message' => sprintf(__('Enable %s header.', 'wp-oauth-debugger'), $header),
                        'details' => sprintf(__('Add the %s security header to protect against common web vulnerabilities.', 'wp-oauth-debugger'), $header)
                    );
                }
            }

            // Token Lifetimes
            if ($security_status['token_lifetime']['access_token'] > 3600) {
                $recommendations[] = array(
                    'level' => 'warning',
                    'message' => __('Reduce access token lifetime.', 'wp-oauth-debugger'),
                    'details' => __('Consider reducing the access token lifetime to 1 hour or less for better security.', 'wp-oauth-debugger')
                );
            }

            if ($security_status['token_lifetime']['refresh_token'] > 1209600) {
                $recommendations[] = array(
                    'level' => 'warning',
                    'message' => __('Reduce refresh token lifetime.', 'wp-oauth-debugger'),
                    'details' => __('Consider reducing the refresh token lifetime to 14 days or less.', 'wp-oauth-debugger')
                );
            }

            if (empty($recommendations)): ?>
                <p class="oauth-debugger-notice success">
                    <?php _e('No security recommendations at this time. Your OAuth configuration appears to be secure.', 'wp-oauth-debugger'); ?>
                </p>
            <?php else: ?>
                <?php foreach ($recommendations as $recommendation): ?>
                    <div class="oauth-debugger-recommendation oauth-debugger-level-<?php echo $recommendation['level']; ?>">
                        <h3><?php echo esc_html($recommendation['message']); ?></h3>
                        <p><?php echo esc_html($recommendation['details']); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
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

.oauth-debugger-security-score {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
}

.oauth-debugger-score-circle {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
    font-weight: bold;
}

.oauth-debugger-score-circle.success {
    background: #dff0d8;
    color: #3c763d;
}

.oauth-debugger-score-circle.warning {
    background: #fcf8e3;
    color: #8a6d3b;
}

.oauth-debugger-score-circle.error {
    background: #f2dede;
    color: #a94442;
}

.oauth-debugger-score-value {
    font-size: 36px;
    line-height: 1;
    margin-bottom: 5px;
}

.oauth-debugger-score-label {
    font-size: 14px;
    font-weight: normal;
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

.oauth-debugger-recommendations {
    margin-top: 20px;
}

.oauth-debugger-recommendation {
    background: #f8f9fa;
    border-left: 4px solid;
    padding: 15px;
    margin-bottom: 15px;
}

.oauth-debugger-recommendation h3 {
    margin: 0 0 10px 0;
    font-size: 16px;
}

.oauth-debugger-recommendation p {
    margin: 0;
    color: #666;
}

.oauth-debugger-level-error {
    border-color: #dc3545;
}

.oauth-debugger-level-warning {
    border-color: #ffc107;
}

.oauth-debugger-level-success {
    border-color: #28a745;
}

.oauth-debugger-notice {
    padding: 15px;
    background: #f8f9fa;
    border-left: 4px solid;
    margin: 0;
}

.oauth-debugger-notice.success {
    border-color: #28a745;
}
</style> 