<?php

namespace ArticleVoting;

/**
 * Class ArticleVotingAdmin
 *
 * This class handles the administration of the Article Voting plugin.
 * It adds menu items for the main page, statistics, and settings.
 *
 * @package ArticleVoting
 */
class ArticleVotingAdmin {

    /**
     * ArticleVotingAdmin constructor.
     *
     * Initializes the class and adds the admin menu.
     */
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
    }

    /**
     * add_admin_menu function.
     *
     * Adds the admin menu items for the main page, statistics, and settings.
     *
     * @param string $parent_slug The parent slug for the admin menu.
     * @param string $capability The capability required to access the admin menu.
     */
    public function add_admin_menu($parent_slug = 'article-voting', $capability = 'manage_options') {
        add_menu_page(
            'Article Voting', // Page title
            'Article Voting', // Menu title
            $capability, // Capability required
            $parent_slug, // Menu slug
            [$this, 'render_main_page'], // Function to render the page content
            'dashicons-chart-line', // Icon URL or Dashicon
            26 // Position in the menu
        );

        add_submenu_page(
            $parent_slug, // Parent slug
            'Statistics', // Page title
            'Statistics', // Menu title
            $capability, // Capability required
            'article-voting-stats', // Menu slug
            [$this, 'render_stats_page'] // Callback function to render the page content
        );

        add_submenu_page(
            $parent_slug, // Parent slug
            'Settings', // Page title
            'Settings', // Menu title
            $capability, // Capability required
            'article-voting-settings', // Menu slug
            [$this, 'render_settings_page'] // Callback function to render the page content
        );
    }

    /**
     * render_main_page function.
     *
     * Renders the main page of the Article Voting plugin.
     *
     * @return void
     */
    public function render_main_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Article Voting Plugin', 'article-voting-plugin'); ?></h1>
            <p><?php esc_html_e('The Article Voting Plugin allows you to gather feedback from your readers by enabling them to vote on your blog posts.', 'article-voting-plugin'); ?><br>
            <?php esc_html_e('Readers can submit a positive or negative vote, and the plugin will display the overall voting results on the post page.', 'article-voting-plugin'); ?></p>
            <p><?php esc_html_e('With this plugin, you can:', 'article-voting-plugin'); ?></p>
            <ul>
                <li><?php esc_html_e('- Display a voting interface on your blog posts', 'article-voting-plugin'); ?></li>
                <li><?php esc_html_e('- Track the number of positive and negative votes for each post', 'article-voting-plugin'); ?></li>
                <li><?php esc_html_e('- View voting statistics and percentages for all posts', 'article-voting-plugin'); ?></li>
                <li><?php esc_html_e('- Configure plugin settings and enable/disable test mode', 'article-voting-plugin'); ?></li>
            </ul>
        </div>
        <?php
    }

    /**
     * render_stats_page function.
     *
     * Renders the statistics page of the Article Voting plugin.
     *
     * @return void
     */
    public function render_stats_page() {
        $posts = get_posts(array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => -1
        ));

        $voting_data = []; // Define the $voting_data array

        foreach ($posts as $post) {
            $positive_votes = intval(get_post_meta($post->ID, 'positive_votes', true));
            $negative_votes = intval(get_post_meta($post->ID, 'negative_votes', true));
            $total_votes = $positive_votes + $negative_votes;
            $percentageOfLikes = $total_votes > 0 ? round(($positive_votes / $total_votes) * 100) : 0;

            // Process the voting data for each post
            $voting_data[$post->post_title] = [
                'positive_votes' => $positive_votes,
                'negative_votes' => $negative_votes,
                'total_votes' => $total_votes,
                'percentageOfLikes' => $percentageOfLikes,
            ];
        }

        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Voting Statistics', 'article-voting-plugin'); ?></h1>

            <p><?php esc_html_e('This page displays the voting statistics for all posts on your blog.', 'article-voting-plugin'); ?></p>

            <p><?php esc_html_e('The table below shows the number of positive and negative votes for each post.', 'article-voting-plugin'); ?></p>
            <table class="tablo"style="width: 100%; border: 1px solid #ccc;">
                <tr>
                    <th style="background-color: grey; color: white; padding: 10px;"><?php esc_html_e('Post Title', 'article-voting-plugin'); ?></th>
                    <th style="background-color: grey; color: white; padding: 10px;"><?php esc_html_e('Positive Votes', 'article-voting-plugin'); ?></th>
                    <th style="background-color: grey; color: white; padding: 10px;"><?php esc_html_e('Negative Votes', 'article-voting-plugin'); ?></th>
                    <th style="background-color: grey; color: white; padding: 10px;"><?php esc_html_e('Total Votes', 'article-voting-plugin'); ?></th>
                    <th style="background-color: grey; color: white; padding: 10px;"><?php esc_html_e('Percentage of Likes', 'article-voting-plugin'); ?></th>
                </tr>

        <?php
        foreach ($voting_data as $post_title => $stats) {
            ?>
                <tr>
                    <td style="text-align: center; padding: 10px; background-color: lightgrey; color: grey;"><?php echo esc_html($post_title); ?></td>
                    <td style="text-align: center; padding: 10px; background-color: lightgrey; color: grey;"><?php echo esc_html($stats['positive_votes']); ?></td>
                    <td style="text-align: center; padding: 10px; background-color: lightgrey; color: grey;"><?php echo esc_html($stats['negative_votes']); ?></td>
                    <td style="text-align: center; padding: 10px; background-color: lightgrey; color: grey;"><?php echo esc_html($stats['total_votes']); ?></td>
                    <td style="text-align: center; padding: 10px; background-color: lightgrey; color: grey;"><?php echo esc_html($stats['percentageOfLikes']); ?></td>
                </tr>
            <?php
        }
        ?>
            </table>
        </div>
        <?php
    }
    /**
     * render_settings_page function.
     *
     * Renders the settings page of the Article Voting plugin.
     *
     * @return void
     */
    public function render_settings_page() {
        // Render the settings page HTML
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Voting Plugin Settings', 'article-voting-plugin'); ?></h1>
    
            <p><?php esc_html_e('You can configure the plugin settings here.', 'article-voting-plugin'); ?></p>
        </div>
        <?php
    }

}
