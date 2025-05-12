<?php

namespace WP_OAuth_Debugger\Admin\Pages;

/**
 * Base class for all admin pages
 */
abstract class BasePage {
    /**
     * Render the page
     */
    abstract public function render();

    /**
     * Check if the current user has permission to access this page
     *
     * @return bool
     */
    protected function check_permissions() {
        return current_user_can('manage_options');
    }

    /**
     * Get the page title
     *
     * @return string
     */
    abstract protected function get_page_title();

    /**
     * Get the page icon
     *
     * @return string
     */
    protected function get_page_icon() {
        return 'admin-generic';
    }

    /**
     * Render the page header
     */
    protected function render_header() {
?>
        <div class="wrap oauth-debugger">
            <div class="oauth-debugger-header">
                <h1>
                    <span class="dashicons dashicons-<?php echo esc_attr($this->get_page_icon()); ?>"></span>
                    <?php echo esc_html($this->get_page_title()); ?>
                </h1>
                <div class="oauth-debugger-header-actions">
                    <?php $this->render_header_actions(); ?>
                </div>
            </div>
        <?php
    }

    /**
     * Render the page footer
     */
    protected function render_footer() {
        ?>
        </div><!-- .wrap -->
    <?php
    }

    /**
     * Render header actions (buttons, links, etc.)
     */
    protected function render_header_actions() {
        // Override in child classes if needed
    }

    /**
     * Render a notice message
     *
     * @param string $message The message to display
     * @param string $type    The type of notice (success, error, warning, info)
     */
    protected function render_notice($message, $type = 'info') {
    ?>
        <div class="notice notice-<?php echo esc_attr($type); ?> is-dismissible">
            <p><?php echo esc_html($message); ?></p>
        </div>
    <?php
    }

    /**
     * Render a card container
     *
     * @param string $title   The card title
     * @param string $content The card content
     * @param array  $args    Additional arguments
     */
    protected function render_card($title, $content, $args = array()) {
        $args = wp_parse_args($args, array(
            'icon' => '',
            'class' => '',
            'footer' => ''
        ));
    ?>
        <div class="oauth-debugger-card <?php echo esc_attr($args['class']); ?>">
            <div class="oauth-debugger-card-header">
                <h2>
                    <?php if ($args['icon']) : ?>
                        <span class="dashicons dashicons-<?php echo esc_attr($args['icon']); ?>"></span>
                    <?php endif; ?>
                    <?php echo esc_html($title); ?>
                </h2>
            </div>
            <div class="oauth-debugger-card-body">
                <?php echo $content; ?>
            </div>
            <?php if ($args['footer']) : ?>
                <div class="oauth-debugger-card-footer">
                    <?php echo $args['footer']; ?>
                </div>
            <?php endif; ?>
        </div>
<?php
    }
}
