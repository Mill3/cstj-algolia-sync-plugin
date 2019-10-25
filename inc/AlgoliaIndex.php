<?php

/**
 * This file is part of WpAlgolia plugin.
 * (c) Antoine Girard for Mill3 Studio <antoine@mill3.studio>
 * @version 0.0.2
 * @since 0.0.2
 */

namespace WpAlgolia;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

// const CACHE_KEY = "wp-algolia-index-initialized";
// const CACHE_GROUP =

class AlgoliaIndex
{
    public $index_settings;

    public $index = null;

    public $index_name;

    public $algolia_client;

    public $post_type;

    private $log;

    public function __construct($index_name, $algolia_client, $index_settings = array('config' => array()))
    {
        $this->index_name = $index_name;
        $this->algolia_client = $algolia_client;
        $this->index_settings = $index_settings;
        $this->post_type = $index_settings['post_type'];

        // create logging
        $this->log = new Logger($index_name);
        $this->log->pushHandler(new StreamHandler(__DIR__."/debug-index-{$index_name}.log", Logger::DEBUG));

        // try to retrieve cached index first
        $this->init_index();

        // add_action('wp_algolia_update_record', array($this, 'update_record_action'), 10, 2);
    }

    public function clear()
    {
        $this->index->clearObjects()->wait();
    }

    public function save($postID, $post)
    {
        $data = array(
            'objectID'          => $this->index_objectID($postID),
            'post_title'        => $post->post_title,
            'post_thumbnail'    => get_the_post_thumbnail_url($post, 'post-thumbnail'),
            'excerpt'           => $this->prepareTextContent($post->post_excerpt),
            'content'           => $this->prepareTextContent($post->post_content),
            'url'               => get_post_permalink($post->ID),
        );

        // append each custom field values
        foreach ($this->index_settings['acf_fields'] as $key => $field) {
            $data[$field] = $this->prepareTextContent(get_field($field, $postID));
        }

        // append extra taxonomies
        foreach ($this->index_settings['taxonomies'] as $key => $taxonomy) {
            $data[$taxonomy] = wp_get_post_terms($post->ID, $taxonomy, array('fields' => 'names'));
        }

        $this->log->info('Saving object : '.$this->index_objectID($postID));

        // TODO : create transient

        // save object
        $this->index->saveObject($data);
    }

    public function delete($postID)
    {
        $this->index->deleteObject($this->index_objectID($postID));
        $this->delete_cached_object($postID);
    }

    public function record_exist($postID)
    {
        $objectID = $this->index_objectID($postID);
        $cached_object = $this->get_cached_object($postID);

        if ( !$cached_object ) {
            try {
                $object = $this->index->getObject($objectID, array('attributesToRetrieve' => 'objectID'));
                $this->cache_object($postID);
                return true;
            } catch (\Throwable $th) {
                return false;
            }
        } else {
            return $cached_object;
        }

    }

    private function init_index()
    {
        $cached_index = $this->get_cached_index();

        // cache found, set stored value to class
        if ( $cached_index ) {
            $this->log->info('Use cached index');

            $this->index = $cached_index;
            return;

        // no cache is set, create index with settings
        } else {
            $this->log->info('Create index');

            // init index in Algolia
            $this->index = $this->algolia_client->initIndex($this->index_name);

            // set settings
            $this->index->setSettings($this->index_settings['config']);


            // trigger cache storage
            $this->cache_index();
        }
    }

    private function index_objectID($postID)
    {
        return implode('_', array($this->index_settings['post_type'], $postID));
    }

    private function prepareTextContent($content)
    {
        $content = strip_tags($content);
        $content = preg_replace('#[\n\r]+#s', ' ', $content);

        return $content;
    }

    public function cache_index() {
        set_transient($this->cache_key_index(), $this->index, 3600);
    }

    public function get_cached_index() {
        return get_transient($this->cache_key_index());
    }

    public function cache_object($postID) {
        set_transient($this->cache_key_object($postID), true, 3600);
    }

    public function get_cached_object($postID) {
        return get_transient($this->cache_key_object($postID));
    }

    public function delete_cached_object($postID) {
        return delete_transient($this->cache_key_object($postID));
    }

    public function cache_key_index() {
        return "wp-algolia-index-initialized-{$this->index_name}";
    }

    public function cache_key_object($postID) {
        return "wp-algolia-index-object-{$this->post_type}-{$postID}";
    }

}
