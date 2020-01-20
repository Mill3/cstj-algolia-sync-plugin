<?php

/**
 * This file is part of WpAlgolia plugin.
 * (c) Antoine Girard for Mill3 Studio <antoine@mill3.studio>
 * @version 0.0.7
 */

namespace WpAlgolia;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

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
        $this->log->pushHandler(new StreamHandler(__DIR__."/debug-{$index_name}.log", Logger::DEBUG));

        // Core's WP actions
        // add_action("save_post_{$this->post_type}", array($this, 'save_post'), 10, 2);
        add_action('wp_insert_post', array($this, 'save_post'), 10, 3);
        add_action("delete_post_{$this->post_type}", array($this, 'delete_post'), 10, 2);

        // add bulk action to post type
        add_filter("bulk_actions-edit-{$this->post_type}", array($this, 'register_bulk_update'), 10, 3);
        add_filter("handle_bulk_actions-edit-{$this->post_type}", array($this, 'handle_bulk_update'), 10, 3);

        // add extra column in admin
        add_filter("manage_{$this->post_type}_posts_columns", array($this, 'manage_admin_columns'), 10, 3);
        add_filter("manage_{$this->post_type}_posts_custom_column", array($this, 'manage_admin_column'), 10, 3);

        // Taxonomies action
        foreach ($this->index_settings['taxonomies'] as $key => $taxonomy) {
            add_action("edited_{$taxonomy}", array($this, 'update_posts'), 10, 2);
            add_action("delete_{$taxonomy}", array($this, 'update_posts'), 10, 2);
        }
    }

    public function get_post_type()
    {
        return $this->post_type;
    }

    public function save_post($post_ID, $post)
    {
        // do_action('wp_algolia_update_record', $post_ID);
        $this->log->info('Updating ? '.print_r($post, true));

        if (wp_is_post_autosave($post)) {
            return;
        }

        if ($this->get_post_type() !== $post->post_type) {
            return;
        }

        if ('publish' !== $post->post_status) {
            $this->log->info('Removing record : '.$post_ID);
            $this->algolia_index->delete($post_ID, $post);

            return;
        }

        // should push to index ?
        if (false === $this->show_in_index($post_ID)) {
            $this->log->info('Removing record : '.$post_ID);
            $this->algolia_index->delete($post_ID, $post);

            return;
        }

        // pass all conditions, then save
        $this->log->info('Saving record : '.$post_ID);

        $this->algolia_index->save($post_ID, $post);
    }

    public function delete_post($postID, $post)
    {
        $this->algolia_index->delete($postID, $post);
    }

    public function register_bulk_update($bulk_actions)
    {
        $bulk_actions['wpalgolia_index_update'] = __('Send to Algolia index', 'wp_algolia');
        $bulk_actions['wpalgolia_index_delete'] = __('Remove from Algolia index', 'wp_algolia');

        return $bulk_actions;
    }

    public function handle_bulk_update($redirect_to, $doaction, $post_ids)
    {
        foreach ($post_ids as $post_ID) {
            $post = get_post($post_ID);
            if ($post) {
                switch ($doaction) {
                    case 'wpalgolia_index_update':
                        // should push to index ?
                        if (true === $this->show_in_index($post_ID)) {
                            $this->algolia_index->save($post_ID, $post);
                        }
                        break;

                    case 'wpalgolia_index_delete':
                        $this->algolia_index->delete($post_ID);
                        break;
                }
            }
        }

        return $redirect_to;
    }

    public function manage_admin_columns($columns)
    {
        $columns['in_index'] = __('Algolia Index', 'wp_algolia');

        return $columns;
    }

    public function manage_admin_column($column, $post_id)
    {
        if ('in_index' === $column) {
            $in_index = $this->algolia_index->record_exist($post_id);
            echo $in_index ? '<span class="dashicons dashicons-yes-alt" style="color: #5468ff;"></span>' : '';
        }
    }

    public function update_posts()
    {
        $posts = get_posts(array(
            'post_type'   => $this->get_post_type(),
            'numberposts' => -1,
            'meta_query' => array(
                array(
                    'key'     => $this->index_settings['hidden_flag_field'],
                    'compare' => '=',
                    'value'   => 1,
                )
            ),
        ));

        // update each posts from current post type
        foreach ($posts as $key => $post) {
            $this->algolia_index->save($post->ID, $post);
        }
    }

    public function save_all()
    {
        // TODO: implement for cli
    }

    /**
     * Check if post has a bool 'hidden_flag_field' returning true
     * Any other value should prevent from sending object to Algolia.
     *
     * @param [type] $post_ID
     *
     * @return bool
     */
    public function show_in_index($post_ID)
    {
        // return ACF field value
        return get_field($this->index_settings['hidden_flag_field'], $post_ID);
    }
}
