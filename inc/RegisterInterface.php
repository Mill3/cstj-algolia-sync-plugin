<?php

/**
 * This file is part of WpAlgolia plugin.
 * (c) Antoine Girard for Mill3 Studio <antoine@mill3.studio>
 * @version 0.0.2
 * @since 0.0.2
 */


namespace WpAlgolia;

interface RegisterInterface
{
    public function get_post_type();

    public function save_post($postId, $post);

    public function delete_post($postId, $post);

    public function update_posts();

    public function register_bulk_update($bulk_actions);

    public function handle_bulk_update($redirect_to, $doaction, $post_ids);

    public function manage_admin_columns($columns);

    public function manage_admin_column($column, $post_id);

    public function save_all();
}
