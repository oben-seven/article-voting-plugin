<?php
/**
 * Plugin Name: Find.co - Vote For Article
 * Plugin URI: http://obenseven.com/vote-for-article/
 * Description: This plugin was created as part of the find.co plugin development case.
 * Version: 1.0.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: Oben Seven
 * Author URI: http://obenseven.com
 * License: GPLv2 or later
 * Update URI: http://obenseven.com/vote-for-article/
 * Text Domain: voteforarticle
 * Domain Path: /languages
 */

/**
 * Exit if accessed directly.
 *
 * @since 1.0.0
 */
if (!defined('ABSPATH')) {
    exit;
}


/**
 * Include the necessary classes.
 *
 * @since 1.0.0
 */
require_once plugin_dir_path(__FILE__) . 'includes/class-article-voting-plugin.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-article-voting-test.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-article-voting-admin.php';

/**
 * Use the ArticleVoting namespace.
 *
 * @since 1.0.0
 */
use ArticleVoting\ArticleVotingPlugin;
use ArticleVoting\ArticleVotingTest;
use ArticleVoting\ArticleVotingAdmin;

/**
 * Initialize the Article Voting Plugin.
 *
 * @since 1.0.0
 */
$article_voting_plugin = new ArticleVoting\ArticleVotingPlugin();

/**
 * Initialize the Article Voting Test class for development purposes.
 *
 * @since 1.0.0
 */
$articleVotingTest = new ArticleVoting\ArticleVotingTest();

/**
 * Initialize the Article Voting Admin class if on the admin side.
 *
 * @since 1.0.0
 */
if (is_admin()) {
    $articleVotingAdmin = new ArticleVoting\ArticleVotingAdmin();
}