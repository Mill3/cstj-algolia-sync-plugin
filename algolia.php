<?php

/**
 * GitHub Plugin URI:  Mill3/cstj-algolia-sync-plugin
 * GitHub Plugin URI:  https://github.com/Mill3/cstj-algolia-sync-plugin
 * Plugin Name: CSTJ - Algolia Sync
 * Description: Sync data from Wordpress to Algolia
 * Version: 0.0.7
 * Author Name: Mill3 Studio (Antoine Girard)
 *
 * @package CSTJ_Algolia_Sync
 */

namespace WpAlgolia;

class Main {

    private $algolia_client;

    private $registered_post_types = array();

    public function __construct($algolia_client) {
        $this->algolia_client = $algolia_client;
    }

    public function run() {
        $this->register();
        $this->cli();
    }

    public static function search() {
        return "foobar";
    }

    private function register() {
        $registered_post_types['programs'] = new \WpAlgolia\Register\Posts('post', ALGOLIA_PREFIX . 'post', $this->algolia_client);
        $registered_post_types['page'] = new \WpAlgolia\Register\Pages('page', ALGOLIA_PREFIX . 'page', $this->algolia_client);
        $registered_post_types['post'] = new \WpAlgolia\Register\Programs('programs', ALGOLIA_PREFIX . 'programs', $this->algolia_client);
    }

    private function cli() {
        // register cli commands
        if (defined('WP_CLI') && WP_CLI) {
            echo "cli should work!";
        }
    }

}

add_action(
    'plugins_loaded',
    function () {

        if(!defined('ALGOLIA_APPLICATION_ID') || !defined('ALGOLIA_ADMIN_API_KEY')) {
            // Unless we have access to the Algolia credentials, stop here.
            return;
        }

        if(!defined('ALGOLIA_PREFIX')) {
            define('ALGOLIA_PREFIX', 'prod_');
        }

        require_once __DIR__ . '/vendor/autoload.php';
        require_once __DIR__ . '/inc/AlgoliaIndex.php';
        require_once __DIR__ . '/inc/RegisterAbstract.php';
        require_once __DIR__ . '/inc/RegisterInterface.php';
        require_once __DIR__ . '/post_types/Posts.php';
        require_once __DIR__ . '/post_types/Pages.php';
        require_once __DIR__ . '/post_types/Programs.php';
        // require_once __DIR__ . '/wp-cli.php';

        // client
        $algoliaClient = \Algolia\AlgoliaSearch\SearchClient::create(ALGOLIA_APPLICATION_ID, ALGOLIA_ADMIN_API_KEY);

        // instance with supported post types
        $instance = new \WpAlgolia\Main($algoliaClient);

        // run
        $instance->run();

        // function wpalgolia_search() {
        //     $instance->search();
        // }

        // // WP CLI commands.
        // if (defined('WP_CLI') && WP_CLI) {
        //     require_once 'inc/Commands.php';
        //     $commands = new \WpAlgolia\Commands($indexRepository);
        //     \WP_CLI::add_command('algolia', $commands);
        // }

    }
);
