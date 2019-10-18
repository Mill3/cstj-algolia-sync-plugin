<?php

/**
 * This file is part of WpAlgolia plugin.
 * (c) Antoine Girard for Mill3 Studio <antoine@mill3.studio>
 * @version 0.0.1
 * @since 0.0.1
 */

namespace WpAlgolia\Register;

interface RegisterInterface
{
    public function get_post_type();
    public function get_index_name();
    public function save_record($postId, $post);
}
