<?php

/**
 * This file is part of WpAlgolia plugin.
 * (c) Antoine Girard for Mill3 Studio <antoine@mill3.studio>
 * @version 0.0.1
 * @since 0.0.1
 */

namespace WpAlgolia\Register;

use WpAlgolia\RegisterAbstract as WpAlgoliaRegisterAbstract;
use WpAlgolia\RegisterInterface as WpAlgoliaRegisterInterface;

class Posts extends WpAlgoliaRegisterAbstract implements WpAlgoliaRegisterInterface
{
    public $index_config = array(
        'acf_fields' => array(),
        'taxonomies' => array(),
        'config'     => array(
            'searchableAttributes' => array('post_title'),
            'customRanking'        => array('desc(post_title)'),
            ),
            array(
                'forwardToReplicas' => true,
            ),
    );

    public function __construct($post_type, $index_name, $algolia_client)
    {
        parent::__construct($post_type, $index_name, $algolia_client, $this->index_config);
    }

}
