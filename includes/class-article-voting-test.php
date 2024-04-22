<?php
/**
 * Namespace for ArticleVoting plugin.
 */
namespace ArticleVoting;

/**
 * Class for handling article voting functionality.
 */
class ArticleVotingTest {

    /**
     * Constructor for the ArticleVotingTest class.
     * Adds AJAX actions for resetting votes.
     */
    public function __construct() {
        add_action('wp_ajax_reset_vote', array($this, 'handle_reset_vote'));
        add_action('wp_ajax_nopriv_reset_vote', array($this, 'handle_reset_vote'));
    }

    /**
     * Display voting UI on single post pages.
     *
     * @param string $content The content of the post.
     * @return string The modified content with voting UI.
     */
    public function display_voting_ui($content) {
        if (is_single() && current_user_can('manage_options')) {
            $voting_ui = '<div class="article-voting-reset">';
            $voting_ui .= '<button class="reset-vote">RESET</button>';
            $voting_ui .= '</div>';

            $content .= $voting_ui;
        }

        return $content;
    }

    /**
     * Reset the vote data for a given post ID.
     *
     * @param int $post_id The ID of the post to reset the vote data for.
     * @return bool|WP_Error True if the vote data is successfully reset, WP_Error object if the post ID is invalid.
     */
    public function reset_vote($post_id) {
        // Check if the post exists
        $post = get_post($post_id);
        if (!$post) {
            return new WP_Error('invalid_post_id', 'Invalid post ID');
        }

        // Delete the existing vote data
        delete_post_meta($post_id, 'positive_votes');
        delete_post_meta($post_id, 'negative_votes');
        delete_post_meta($post_id, 'voted_ips');

        return true;
    }

    /**
     * Handle the AJAX request to reset the vote data.
     *
     * @return void
     */
    public function handle_reset_vote() {
        // Check if the request is valid
        if (!isset($_POST['post_id']) || !is_numeric($_POST['post_id'])) {
            wp_send_json_error('Invalid post ID');
        }

        $post_id = intval($_POST['post_id']);
        $result = $this->reset_vote($post_id);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        } else {
            wp_send_json_success('Vote data reset successfully');
        }
    }

}