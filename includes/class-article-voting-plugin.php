<?php

namespace ArticleVoting;

/**
 * Class responsible for the Article Voting functionality.
 */
class ArticleVotingPlugin {
    /**
     * Constructor for the ArticleVotingPlugin class.
     *
     * Initializes the necessary hooks and filters for the plugin.
     */
    public function __construct() {
        // Initialization code goes here
        add_filter('the_content', array($this, 'display_voting_ui'));
        add_action('wp_ajax_save_vote', array($this, 'save_vote'));
        add_action('wp_ajax_nopriv_save_vote', array($this, 'save_vote'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('add_meta_boxes', array($this, 'add_voting_meta_box'));
        // Check the user already voted state
        add_action('wp_ajax_get_voting_percentage', array($this, 'get_voting_percentage'));
        add_action('wp_ajax_nopriv_get_voting_percentage', array($this, 'get_voting_percentage'));

        $article_voting_test = new ArticleVotingTest();
        add_filter('the_content', array($article_voting_test, 'display_voting_ui'));

    }

    /**
     * Adds the voting UI to the content of a post.
     *
     * Checks if the post is a single post and adds the voting UI if it is.
     *
     * @param string $content The content of the post.
     *
     * @return string The modified content of the post with the voting UI.
     */
    public function display_voting_ui($content) {
        // Check if the test mode is enabled
        if (is_single()) {
            $voting_ui = '<div class="article-voting">';
            $voting_ui .= '<p>WAS THIS ARTICLE HELPFUL?</p>';
            $voting_ui .= '<button class="vote-yes">&#128516; YES</button>';
            $voting_ui .= '<button class="vote-no">&#128542; NO</button>';
            $voting_ui .= '</div>';

            $content .= $voting_ui;
        } else {
            $content .= '<p>This is not a single post page.</p>';
        }

        return $content;
    }

    /**
     * Saves the user's vote for an article.
     *
     * Checks if the request is valid and if the user has already voted.
     *
     * @return void
     */
    public function save_vote() {
        // Check if the request is valid
        if (!isset($_POST['post_id']) || !is_numeric($_POST['post_id'])) {
            wp_send_json_error('Invalid post ID');
        }

        if (!isset($_POST['vote']) || !in_array($_POST['vote'], array('yes', 'no'))) {
            wp_send_json_error('Invalid vote value');
        }

        $post_id = intval($_POST['post_id']);
        $vote = sanitize_text_field($_POST['vote']);

        // Check if the visitor has already voted
        $visitor_ip = $_SERVER['REMOTE_ADDR'];
        $voted_ips = get_post_meta($post_id, 'voted_ips', true);
        $voted_ips = $voted_ips ? $voted_ips : array();

        if (in_array($visitor_ip, $voted_ips)) {
            wp_send_json_error('You have already voted for this article.');
        }

        // Save the vote
        $positive_votes = intval(get_post_meta($post_id, 'positive_votes', true));
        $negative_votes = intval(get_post_meta($post_id, 'negative_votes', true));

        if ($vote === 'yes') {
            update_post_meta($post_id, 'positive_votes', $positive_votes + 1);
        } else {
            update_post_meta($post_id, 'negative_votes', $negative_votes + 1);
        }

        // Store the visitor's IP to prevent multiple votes
        $voted_ips[] = $visitor_ip;
        update_post_meta($post_id, 'voted_ips', $voted_ips);

        // Calculate and return the voting results
        $total_votes = $positive_votes + $negative_votes;
        $percentage = $total_votes > 0 ? round(($positive_votes / $total_votes) * 100) : 0;

        wp_send_json_success(array(
            'percentage' => $percentage,
            'positive_votes' => $positive_votes + ($vote === 'yes' ? 1 : 0),
            'negative_votes' => $negative_votes + ($vote === 'no' ? 1 : 0),
            'total_votes' => $total_votes + 1,
        ));
    }

    /**
     * Enqueues the necessary scripts and styles for the voting functionality.
     *
     * @return void
     */
    public function enqueue_scripts() {
        $plugin_dir_url = dirname(plugin_dir_url(__FILE__));
        $plugin_assets_url = $plugin_dir_url . '/' . 'assets/';

        wp_enqueue_style('article-voting', $plugin_assets_url . 'css/article-voting.min.css');
        wp_enqueue_script('article-voting', $plugin_assets_url . 'js/article-voting.min.js', array('jquery'), '1.0', true);
        wp_enqueue_script('article-voting-test', $plugin_assets_url . 'js/voting-test.min.js', array('jquery'), '1.0', true);

        $post_id = get_the_ID(); // Get the current post ID

        wp_localize_script('article-voting', 'article_voting_params', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'post_id' => $post_id, // Pass the post_id to the JavaScript file
        ));
    }

    /**
     * Renders the voting meta box for a post.
     *
     * @param WP_Post $post The post object.
     *
     * @return void
     */
    public function render_voting_meta_box($post) {
        $post_id = isset($post->ID) ? $post->ID : 0;
        $positive_votes = intval(get_post_meta($post_id, 'positive_votes', true));
        $negative_votes = intval(get_post_meta($post_id, 'negative_votes', true));
        $total_votes = $positive_votes + $negative_votes;
        $percentage = $total_votes > 0 ? round(($positive_votes / $total_votes) * 100) : 0;
        $negative_percentage = $total_votes > 0 ? round(($negative_votes / $total_votes) * 100) : 0;

        echo '<div class="article-voting-meta-box">';
        echo '<p><strong>Positive Votes:</strong> ' . $positive_votes . '</p>';
        echo '<p><strong>Negative Votes:</strong> ' . $negative_votes . '</p>';
        echo '<p><strong>Total Votes:</strong> ' . $total_votes . '</p>';
        echo '<p><strong>Happy Visitor Percentage:</strong><button class="vote-yes">&#128516; ' . $percentage . '%</button></p>';
        echo '<p><strong>Unhappy Visitor Percentage:</strong><button class="vote-no">&#128542; ' . $negative_percentage . '%</button></p>';
        echo '</div>';
    }

    /**
     * Adds the voting meta box for a post.
     *
     * Adds a meta box to display the voting results for a post.
     *
     * @return void
     */
    public function add_voting_meta_box() {
        add_meta_box(
            'article-voting-meta-box',
            'Article Voting Results',
            array($this, 'render_voting_meta_box'),
            'post',
            'side',
            'high'
        );
    }
    /**
     * Gets the voting percentage for a post.
     *
     * Retrieves the voting data from the post meta and calculates the voting percentage.
     *
     * @return void
     */
    public function get_voting_percentage() {
        // Check if the request is valid
        if (!isset($_POST['post_id']) || !is_numeric($_POST['post_id'])) {
            wp_send_json_error('Invalid post ID');
        }

        $post_id = intval($_POST['post_id']);

        // Get the voting data from the post meta
        $positive_votes = intval(get_post_meta($post_id, 'positive_votes', true));
        $negative_votes = intval(get_post_meta($post_id, 'negative_votes', true));

        // Calculate the voting percentage
        $total_votes = $positive_votes + $negative_votes;
        $percentage = $total_votes > 0 ? round(($positive_votes / $total_votes) * 100) : 0;

        wp_send_json_success(array(
            'percentage' => $percentage,
            'positive_votes' => $positive_votes,
            'negative_votes' => $negative_votes,
            'total_votes' => $total_votes,
        ));
    }
    
}
