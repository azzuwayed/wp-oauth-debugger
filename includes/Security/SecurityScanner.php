<?php

namespace WP_OAuth_Debugger\Security;

use WP_OAuth_Debugger\Debug\DebugHelper;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Enhanced security scanning functionality for OAuth implementations.
 */
class SecurityScanner {
    /**
     * The debug helper instance.
     *
     * @var DebugHelper
     */
    private $debug_helper;

    /**
     * Whether the scanner is running in development mode.
     *
     * @var bool
     */
    private $is_development_mode;

    /**
     * Initialize the security scanner.
     *
     * @param DebugHelper $debug_helper The debug helper instance.
     */
    public function __construct(DebugHelper $debug_helper) {
        $this->debug_helper = $debug_helper;
        $this->is_development_mode = defined('WP_DEBUG') && WP_DEBUG;
    }

    /**
     * Run a comprehensive security scan.
     *
     * @return array
     */
    public function run_security_scan() {
        $scan_results = array(
            'timestamp' => current_time('mysql'),
            'environment' => $this->is_development_mode ? 'development' : 'production',
            'vulnerabilities' => $this->scan_vulnerabilities(),
            'jwt_analysis' => $this->analyze_jwt_implementation(),
            'oauth_config' => $this->analyze_oauth_configuration(),
            'development_checks' => $this->is_development_mode ? $this->run_development_checks() : array(),
            'security_score' => 0,
            'recommendations' => array()
        );

        // Calculate security score
        $scan_results['security_score'] = $this->calculate_security_score($scan_results);

        // Generate recommendations
        $scan_results['recommendations'] = $this->generate_recommendations($scan_results);

        return $scan_results;
    }

    /**
     * Run development-specific security checks.
     *
     * @return array
     */
    private function run_development_checks() {
        $checks = array(
            'debug_mode' => array(
                'enabled' => defined('WP_DEBUG') && WP_DEBUG,
                'severity' => 'warning',
                'description' => 'WordPress debug mode is enabled',
                'details' => 'Debug mode should be disabled in production'
            ),
            'debug_log' => array(
                'enabled' => defined('WP_DEBUG_LOG') && WP_DEBUG_LOG,
                'severity' => 'warning',
                'description' => 'Debug logging is enabled',
                'details' => 'Debug logging should be disabled in production'
            ),
            'debug_display' => array(
                'enabled' => defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY,
                'severity' => 'critical',
                'description' => 'Debug display is enabled',
                'details' => 'Debug display should never be enabled in production'
            ),
            'script_debug' => array(
                'enabled' => defined('SCRIPT_DEBUG') && SCRIPT_DEBUG,
                'severity' => 'warning',
                'description' => 'Script debug mode is enabled',
                'details' => 'Script debug mode should be disabled in production'
            ),
            'local_ssl' => array(
                'enabled' => is_ssl(),
                'severity' => 'info',
                'description' => 'Local SSL certificate detected',
                'details' => 'Using local development SSL certificate'
            ),
            'error_reporting' => array(
                'level' => error_reporting(),
                'severity' => 'warning',
                'description' => 'Error reporting level',
                'details' => 'Current error reporting level: ' . error_reporting()
            ),
            'display_errors' => array(
                'enabled' => ini_get('display_errors'),
                'severity' => 'critical',
                'description' => 'PHP display_errors is enabled',
                'details' => 'Should be disabled in production'
            ),
            'development_constants' => $this->check_development_constants(),
            'local_environment' => $this->check_local_environment(),
            'development_tools' => $this->check_development_tools()
        );

        return $checks;
    }

    /**
     * Check for development-specific constants.
     *
     * @return array
     */
    private function check_development_constants() {
        $constants = array(
            'WP_DEBUG' => defined('WP_DEBUG') ? WP_DEBUG : false,
            'WP_DEBUG_LOG' => defined('WP_DEBUG_LOG') ? WP_DEBUG_LOG : false,
            'WP_DEBUG_DISPLAY' => defined('WP_DEBUG_DISPLAY') ? WP_DEBUG_DISPLAY : false,
            'SCRIPT_DEBUG' => defined('SCRIPT_DEBUG') ? SCRIPT_DEBUG : false,
            'SAVEQUERIES' => defined('SAVEQUERIES') ? SAVEQUERIES : false,
            'WP_ENVIRONMENT_TYPE' => defined('WP_ENVIRONMENT_TYPE') ? WP_ENVIRONMENT_TYPE : 'production'
        );

        $issues = array();
        foreach ($constants as $constant => $value) {
            if ($value && $constant !== 'WP_ENVIRONMENT_TYPE') {
                $issues[] = array(
                    'constant' => $constant,
                    'value' => $value,
                    'severity' => 'warning',
                    'description' => "Development constant {$constant} is enabled",
                    'details' => "This should be disabled in production"
                );
            }
        }

        return array(
            'constants' => $constants,
            'issues' => $issues
        );
    }

    /**
     * Check local environment configuration.
     *
     * @return array
     */
    private function check_local_environment() {
        $checks = array(
            'host' => array(
                'value' => $_SERVER['HTTP_HOST'] ?? '',
                'is_local' => $this->is_local_host($_SERVER['HTTP_HOST'] ?? ''),
                'severity' => 'info',
                'description' => 'Local development host detected',
                'details' => 'Running on local development environment'
            ),
            'ip' => array(
                'value' => $_SERVER['SERVER_ADDR'] ?? '',
                'is_local' => $this->is_local_ip($_SERVER['SERVER_ADDR'] ?? ''),
                'severity' => 'info',
                'description' => 'Local IP address detected',
                'details' => 'Running on local network'
            ),
            'database' => array(
                'host' => DB_HOST,
                'is_local' => $this->is_local_database(DB_HOST),
                'severity' => 'info',
                'description' => 'Local database detected',
                'details' => 'Using local database server'
            )
        );

        return $checks;
    }

    /**
     * Check for development tools and their status.
     *
     * @return array
     */
    private function check_development_tools() {
        $tools = array(
            'xdebug' => array(
                'enabled' => extension_loaded('xdebug'),
                'severity' => 'warning',
                'description' => 'Xdebug is enabled',
                'details' => 'Should be disabled in production'
            ),
            'opcache' => array(
                'enabled' => ini_get('opcache.enable'),
                'severity' => 'info',
                'description' => 'OPcache status',
                'details' => 'OPcache is ' . (ini_get('opcache.enable') ? 'enabled' : 'disabled')
            ),
            'composer_dev' => array(
                'enabled' => file_exists(WP_CONTENT_DIR . '/vendor/composer/installed.json') &&
                    $this->has_dev_dependencies(),
                'severity' => 'warning',
                'description' => 'Development dependencies installed',
                'details' => 'Production should not include development dependencies'
            )
        );

        return $tools;
    }

    /**
     * Check if a host is local.
     *
     * @param string $host The host to check.
     * @return bool
     */
    private function is_local_host($host) {
        return in_array($host, array('localhost', '127.0.0.1', '::1')) ||
            strpos($host, '.local') !== false ||
            strpos($host, '.test') !== false ||
            strpos($host, '.dev') !== false;
    }

    /**
     * Check if an IP is local.
     *
     * @param string $ip The IP to check.
     * @return bool
     */
    private function is_local_ip($ip) {
        return in_array($ip, array('127.0.0.1', '::1')) ||
            strpos($ip, '192.168.') === 0 ||
            strpos($ip, '10.') === 0 ||
            strpos($ip, '172.16.') === 0;
    }

    /**
     * Check if a database host is local.
     *
     * @param string $host The database host to check.
     * @return bool
     */
    private function is_local_database($host) {
        return in_array($host, array('localhost', '127.0.0.1', '::1')) ||
            strpos($host, '.local') !== false;
    }

    /**
     * Check if composer has development dependencies.
     *
     * @return bool
     */
    private function has_dev_dependencies() {
        $composer_file = WP_CONTENT_DIR . '/vendor/composer/installed.json';
        if (!file_exists($composer_file)) {
            return false;
        }

        $installed = json_decode(file_get_contents($composer_file), true);
        if (!$installed) {
            return false;
        }

        foreach ($installed['packages'] ?? array() as $package) {
            if (isset($package['require-dev']) && !empty($package['require-dev'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Scan for common OAuth vulnerabilities.
     *
     * @return array
     */
    private function scan_vulnerabilities() {
        $vulnerabilities = array();

        // Check for CSRF protection
        if (!$this->check_csrf_protection()) {
            $vulnerabilities[] = array(
                'type' => 'csrf',
                'severity' => 'high',
                'description' => 'CSRF protection is not properly implemented',
                'details' => 'Missing or invalid CSRF tokens in OAuth endpoints'
            );
        }

        // Check for open redirect vulnerabilities
        if ($this->check_open_redirect()) {
            $vulnerabilities[] = array(
                'type' => 'open_redirect',
                'severity' => 'high',
                'description' => 'Potential open redirect vulnerability detected',
                'details' => 'Redirect URIs are not properly validated'
            );
        }

        // Check for token exposure
        if ($this->check_token_exposure()) {
            $vulnerabilities[] = array(
                'type' => 'token_exposure',
                'severity' => 'critical',
                'description' => 'Tokens might be exposed in logs or responses',
                'details' => 'Sensitive token information found in logs or responses'
            );
        }

        // Check for insecure storage
        if ($this->check_insecure_storage()) {
            $vulnerabilities[] = array(
                'type' => 'insecure_storage',
                'severity' => 'high',
                'description' => 'Tokens or credentials stored insecurely',
                'details' => 'Tokens or credentials found in insecure storage locations'
            );
        }

        // Check for weak encryption
        if ($this->check_weak_encryption()) {
            $vulnerabilities[] = array(
                'type' => 'weak_encryption',
                'severity' => 'high',
                'description' => 'Weak encryption methods detected',
                'details' => 'Using deprecated or weak encryption algorithms'
            );
        }

        return $vulnerabilities;
    }

    /**
     * Analyze JWT implementation.
     *
     * @return array
     */
    private function analyze_jwt_implementation() {
        $analysis = array(
            'jwt_enabled' => false,
            'algorithm' => null,
            'key_strength' => null,
            'token_structure' => null,
            'vulnerabilities' => array()
        );

        // Check if JWT is being used
        $active_tokens = $this->debug_helper->get_active_tokens();
        if (!empty($active_tokens)) {
            $token = reset($active_tokens);
            if (isset($token['access_token'])) {
                try {
                    // Attempt to decode the token without verification
                    $decoded = JWT::decode($token['access_token'], new Key('', 'none'));
                    $analysis['jwt_enabled'] = true;
                    $analysis['algorithm'] = $decoded->alg ?? 'unknown';
                    $analysis['token_structure'] = $this->analyze_token_structure($decoded);

                    // Check for JWT-specific vulnerabilities
                    if ($analysis['algorithm'] === 'none') {
                        $analysis['vulnerabilities'][] = array(
                            'type' => 'jwt_algorithm',
                            'severity' => 'critical',
                            'description' => 'JWT using "none" algorithm',
                            'details' => 'Tokens can be forged without verification'
                        );
                    }

                    if (isset($decoded->exp) && $decoded->exp - time() > 3600) {
                        $analysis['vulnerabilities'][] = array(
                            'type' => 'jwt_expiry',
                            'severity' => 'medium',
                            'description' => 'Long JWT expiration time',
                            'details' => 'Tokens expire after more than 1 hour'
                        );
                    }
                } catch (\Exception $e) {
                    // Token is not a JWT or is invalid
                    $analysis['jwt_enabled'] = false;
                }
            }
        }

        return $analysis;
    }

    /**
     * Analyze OAuth configuration.
     *
     * @return array
     */
    private function analyze_oauth_configuration() {
        $config = array(
            'grant_types' => $this->get_enabled_grant_types(),
            'client_authentication' => $this->analyze_client_authentication(),
            'token_handling' => $this->analyze_token_handling(),
            'security_measures' => $this->get_security_measures()
        );

        return $config;
    }

    /**
     * Calculate security score based on scan results.
     *
     * @param array $scan_results The scan results.
     * @return int
     */
    private function calculate_security_score($scan_results) {
        $score = 100;
        $weights = array(
            'csrf' => 20,
            'open_redirect' => 15,
            'token_exposure' => 25,
            'insecure_storage' => 20,
            'weak_encryption' => 20,
            'jwt_algorithm' => 15,
            'jwt_expiry' => 10
        );

        foreach ($scan_results['vulnerabilities'] as $vulnerability) {
            if (isset($weights[$vulnerability['type']])) {
                $score -= $weights[$vulnerability['type']];
            }
        }

        foreach ($scan_results['jwt_analysis']['vulnerabilities'] as $vulnerability) {
            if (isset($weights[$vulnerability['type']])) {
                $score -= $weights[$vulnerability['type']];
            }
        }

        return max(0, $score);
    }

    /**
     * Generate security recommendations based on scan results.
     *
     * @param array $scan_results The scan results.
     * @return array
     */
    private function generate_recommendations($scan_results) {
        $recommendations = array();

        // Add development-specific recommendations
        if ($this->is_development_mode) {
            $recommendations = array_merge($recommendations, $this->generate_development_recommendations($scan_results));
        }

        // Add regular vulnerability recommendations
        foreach ($scan_results['vulnerabilities'] as $vulnerability) {
            $recommendations[] = array(
                'priority' => $this->get_priority_from_severity($vulnerability['severity']),
                'title' => $this->get_recommendation_title($vulnerability['type']),
                'description' => $vulnerability['description'],
                'solution' => $this->get_recommendation_solution($vulnerability['type'])
            );
        }

        // Sort recommendations by priority
        usort($recommendations, function ($a, $b) {
            return $b['priority'] - $a['priority'];
        });

        return $recommendations;
    }

    /**
     * Generate development-specific recommendations.
     *
     * @param array $scan_results The scan results.
     * @return array
     */
    private function generate_development_recommendations($scan_results) {
        $recommendations = array();

        // Debug mode recommendations
        if ($scan_results['development_checks']['debug_mode']['enabled']) {
            $recommendations[] = array(
                'priority' => 3,
                'title' => 'Disable Debug Mode in Production',
                'description' => 'WordPress debug mode is enabled',
                'solution' => 'Set WP_DEBUG to false in wp-config.php before deploying to production'
            );
        }

        // Debug display recommendations
        if ($scan_results['development_checks']['debug_display']['enabled']) {
            $recommendations[] = array(
                'priority' => 4,
                'title' => 'Disable Debug Display',
                'description' => 'Debug display is enabled, which can expose sensitive information',
                'solution' => 'Set WP_DEBUG_DISPLAY to false in wp-config.php'
            );
        }

        // Development tools recommendations
        if ($scan_results['development_checks']['development_tools']['xdebug']['enabled']) {
            $recommendations[] = array(
                'priority' => 2,
                'title' => 'Disable Xdebug in Production',
                'description' => 'Xdebug is enabled, which can impact performance',
                'solution' => 'Disable Xdebug in php.ini or use php.ini-production configuration'
            );
        }

        // Local environment recommendations
        if ($scan_results['development_checks']['local_environment']['host']['is_local']) {
            $recommendations[] = array(
                'priority' => 1,
                'title' => 'Update Host Configuration for Production',
                'description' => 'Using local development host',
                'solution' => 'Update site URL and home URL to production domain before deployment'
            );
        }

        // Development dependencies recommendations
        if ($scan_results['development_checks']['development_tools']['composer_dev']['enabled']) {
            $recommendations[] = array(
                'priority' => 2,
                'title' => 'Remove Development Dependencies',
                'description' => 'Development dependencies are installed',
                'solution' => 'Run "composer install --no-dev" before deploying to production'
            );
        }

        return $recommendations;
    }

    /**
     * Get priority level from severity.
     *
     * @param string $severity The severity level.
     * @return int
     */
    private function get_priority_from_severity($severity) {
        $priorities = array(
            'critical' => 4,
            'high' => 3,
            'medium' => 2,
            'low' => 1
        );

        return $priorities[$severity] ?? 1;
    }

    /**
     * Get recommendation title based on vulnerability type.
     *
     * @param string $type The vulnerability type.
     * @return string
     */
    private function get_recommendation_title($type) {
        $titles = array(
            'csrf' => 'Implement CSRF Protection',
            'open_redirect' => 'Fix Open Redirect Vulnerability',
            'token_exposure' => 'Prevent Token Exposure',
            'insecure_storage' => 'Secure Token Storage',
            'weak_encryption' => 'Upgrade Encryption Methods',
            'jwt_algorithm' => 'Use Secure JWT Algorithm',
            'jwt_expiry' => 'Adjust JWT Expiration Time'
        );

        return $titles[$type] ?? 'Address Security Issue';
    }

    /**
     * Get recommendation solution based on vulnerability type.
     *
     * @param string $type The vulnerability type.
     * @return string
     */
    private function get_recommendation_solution($type) {
        $solutions = array(
            'csrf' => 'Implement CSRF tokens for all OAuth endpoints and validate them on each request.',
            'open_redirect' => 'Validate and whitelist redirect URIs, implement strict validation rules.',
            'token_exposure' => 'Ensure tokens are not logged, implement proper token masking, and use secure transmission methods.',
            'insecure_storage' => 'Store tokens in a secure database with encryption, implement proper access controls.',
            'weak_encryption' => 'Use strong encryption algorithms (AES-256-GCM) and proper key management.',
            'jwt_algorithm' => 'Use RS256 or ES256 algorithms for JWT signing, avoid the "none" algorithm.',
            'jwt_expiry' => 'Set appropriate token expiration times (recommended: 1 hour for access tokens).'
        );

        return $solutions[$type] ?? 'Review and address the security issue according to OAuth best practices.';
    }

    // Helper methods for vulnerability checks
    private function check_csrf_protection() {
        // Implementation of CSRF protection check
        return true; // Placeholder
    }

    private function check_open_redirect() {
        // Implementation of open redirect check
        return false; // Placeholder
    }

    private function check_token_exposure() {
        // Implementation of token exposure check
        return false; // Placeholder
    }

    private function check_insecure_storage() {
        // Implementation of insecure storage check
        return false; // Placeholder
    }

    private function check_weak_encryption() {
        // Implementation of weak encryption check
        return false; // Placeholder
    }

    private function get_enabled_grant_types() {
        // Implementation of grant types check
        return array(); // Placeholder
    }

    private function analyze_client_authentication() {
        // Implementation of client authentication analysis
        return array(); // Placeholder
    }

    private function analyze_token_handling() {
        // Implementation of token handling analysis
        return array(); // Placeholder
    }

    private function get_security_measures() {
        // Implementation of security measures check
        return array(); // Placeholder
    }

    private function analyze_token_structure($decoded) {
        // Implementation of token structure analysis
        return array(); // Placeholder
    }
}
