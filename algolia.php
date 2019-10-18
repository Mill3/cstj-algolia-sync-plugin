<?php

/**
 * Plugin Name: CSTJ - Algolia Sync
 * Description: Sync data from Wordpress to Algolia
 * Version: 0.0.1
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
        $this->register();
        $this->cli();
    }

    private function register() {
        $registered_post_types['post'] = new \WpAlgolia\Register\Programs('post', ALGOLIA_PREFIX . 'post');
        $registered_post_types['programs'] = new \WpAlgolia\Register\Posts('programs', ALGOLIA_PREFIX . 'programs');
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
        require_once __DIR__ . '/inc/RegisterAbstract.php';
        require_once __DIR__ . '/inc/RegisterInterface.php';
        require_once __DIR__ . '/post_types/Posts.php';
        require_once __DIR__ . '/post_types/Programs.php';
        // require_once __DIR__ . '/wp-cli.php';

        // client
        $algoliaClient = \Algolia\AlgoliaSearch\SearchClient::create(ALGOLIA_APPLICATION_ID, ALGOLIA_ADMIN_API_KEY);

        // instance with supported post types
        $instance = new \WpAlgolia\Main($algoliaClient, ['post', 'programs']);

        // // Local dependencies.
        // require_once 'inc/InMemoryIndexRepository.php';
        // require_once 'inc/PostsIndex.php';
        // require_once 'inc/WpQueryRecordsProvider.php';

        // // MyCompany dependencies.
        // require_once 'inc/MyCompany/PostRecordsProvider.php';
        // require_once 'inc/MyCompany/PostsIndexSettingsFactory.php';
        // require_once 'inc/MyCompany/PostChangeListener.php';

        // $indexRepository = new \WpAlgolia\InMemoryIndexRepository();
        // $algoliaClient = new \WpAlgolia\Client(ALGOLIA_APPLICATION_ID, ALGOLIA_ADMIN_API_KEY);

        // // Register article index.
        // $settings = new \WpAlgolia\MyCompany\PostsIndexSettingsFactory();
        // $recordsProvider = new \WpAlgolia\MyCompany\PostRecordsProvider();
        // $index = new \WpAlgolia\PostsIndex(ALGOLIA_PREFIX . 'posts', $algoliaClient, $settings->create(), $recordsProvider);
        // new \WpAlgolia\MyCompany\PostChangeListener($index);
        // $indexRepository->add('posts', $index);


        // // WP CLI commands.
        // if (defined('WP_CLI') && WP_CLI) {
        //     require_once 'inc/Commands.php';
        //     $commands = new \WpAlgolia\Commands($indexRepository);
        //     \WP_CLI::add_command('algolia', $commands);
        // }

    }
);
