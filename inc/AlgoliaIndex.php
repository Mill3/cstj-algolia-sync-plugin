<?php

/**
 * This file is part of WpAlgolia plugin.
 * (c) Antoine Girard for Mill3 Studio <antoine@mill3.studio>
 * @version 0.0.1
 * @since 0.0.1
 */

namespace WpAlgolia;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class AlgoliaIndex
{
    public $index_settings;

    public $index;

    public $index_name;

    public $algolia_client;

    public $post_type;

    private $log;

    public function __construct($index_name, $algolia_client, $index_settings = array('config' => array()))
    {
        $this->index_name = $index_name;
        $this->algolia_client = $algolia_client;
        $this->index_settings = $index_settings;

        // create logging
        $this->log = new Logger($index_name);
        $this->log->pushHandler(new StreamHandler(__DIR__."/debug-index-{$index_name}.log", Logger::DEBUG));

        $this->init_index();

        add_action('wp_algolia_update_record', array($this, 'update_record_action'), 10, 2);
    }

    public function clear()
    {
        $this->index->clearObjects()->wait();
    }

    public function update_record_action($postID)
    {
        // todo: implement with taxonomies save trigger
        // $this->log->info('Triggered filter action !' . $this->index_name . $/postID);
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

        // save object
        $this->index->saveObject($data);
    }

    public function delete($postID)
    {
        // $this->log->info('Deleting from index object : '.$this->index_objectID($post));
        $this->index->deleteObject($this->index_objectID($postID));
    }

    public function record_exist($postID) {
        $objectID = $this->index_objectID($postID);
        try {
            return $this->index->getObject($objectID, array('attributesToRetrieve' => 'objectID'));
        } catch (\Throwable $th) {
            return false;
        }
    }

    private function init_index()
    {
        $this->index = $this->algolia_client->initIndex($this->index_name);
        $this->index->setSettings($this->index_settings['config']);
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
}
