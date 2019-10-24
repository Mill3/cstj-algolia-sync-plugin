<?php

/**
 * This file is part of WpAlgolia plugin.
 * (c) Antoine Girard for Mill3 Studio <antoine@mill3.studio>
 * @version 0.0.1
 * @since 0.0.1
 */

namespace WpAlgolia;

interface RegisterInterface
{
    public function get_post_type();
    public function save_post($postId, $post);
    public function delete_post($postId, $post);
    public function update_posts();
    public function save_all();
}
