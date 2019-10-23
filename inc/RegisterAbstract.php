<?php

/**
 * This file is part of WpAlgolia plugin.
 * (c) Antoine Girard for Mill3 Studio <antoine@mill3.studio>
 * @version 0.0.1
 * @since 0.0.1
 */

namespace WpAlgolia;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use WpAlgolia\AlgoliaIndex;

abstract class RegisterAbstract
{
    public $post_type;

    public $index_name;

    public $algolia_client;

    public $algolia_index;

    public $index_settings;

    private $log;

    public function __construct($post_type, $index_name, $algolia_client, $index_settings = null)
    {

        // set class attributes
        $this->post_type = $post_type;
        $this->index_name = $index_name;
        $this->algolia_client = $algolia_client;
        $this->index_settings = $index_settings;

        // instance Algolia index
        $this->algolia_index = new \WpAlgolia\AlgoliaIndex($this->index_name, $this->algolia_client, $this->index_settings);

        // create logging
        $this->log = new Logger($index_name);
        $this->log->pushHandler(new StreamHandler(__DIR__ . "/debug-{$index_name}.log", Logger::DEBUG));

        // Core's WP actions
        add_action("save_post_{$post_type}", array($this, 'save_post'), 10, 2);
        add_action("delete_post_{$post_type}", array($this, 'delete_post'), 10, 2);
    }

    public function get_post_type()
    {
        return $this->post_type;
    }

    public function save_post($postID, $post) {

        do_action('wp_algolia_update_record', $postID);

        if (wp_is_post_autosave($post) || wp_is_post_revision($post)) {
            return;
        }

        if ($this->get_post_type() !== $post->post_type) {
            return;
        }

        if ($post->post_status !== 'publish') {
            $this->log->info('Removing record : ' . $postID);
            $this->algolia_index->delete($postID, $post);
            return;
        }

        $this->log->info('Saving record : ' . $postID);
        $this->algolia_index->save($postID, $post);
    }

    public function delete_post($postID, $post) {
        $this->algolia_index->delete($postID, $post);
    }

    public function save_all() {
        // TODO: implement for cli
    }
}
