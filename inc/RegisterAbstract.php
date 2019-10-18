<?php

/**
 * This file is part of WpAlgolia plugin.
 * (c) Antoine Girard for Mill3 Studio <antoine@mill3.studio>
 * @version 0.0.1
 * @since 0.0.1
 */

namespace WpAlgolia\Register;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

abstract class RegisterAbstract
{
    public $post_type;

    public $index_name;

    public $log;

    public function __construct($post_type, $index_name)
    {
        $this->post_type = $post_type;
        $this->index_name = $index_name;

        $this->log = new Logger($index_name);
        $this->log->pushHandler(new StreamHandler(__DIR__ . "/debug-{$index_name}.log", Logger::DEBUG));

        // when post is saved
        add_action("save_post_{$post_type}", array($this, 'save_record'), 10, 2);
    }

    public function get_post_type()
    {
        return $this->post_type;
    }

    public function get_index_name()
    {
        return $this->index_name;
    }

    public function save_record($postId, $post) {
        // $this->log->pushHandler(new StreamHandler('debug.log', Logger::WARNING));
        $this->log->info('Saving record in : ' . $postId);

        // echo 'post type was saved';
        if (wp_is_post_autosave($post) || wp_is_post_revision($post)) {
            return;
        }
        if ($this->get_post_type() !== $post->post_type) {
            return;
        }

        return true;
    }
}
