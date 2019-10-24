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

    public $acf_fields = array();

    public $taxonomies = array('tags');

    public function __construct($post_type, $index_name, $algolia_client)
    {
        $index_config = array(
            'acf_fields' => $this->acf_fields,
            'taxonomies' => $this->taxonomies,
            'config'     => array(
                'searchableAttributes'  => $this->searchableAttributes(),
                'queryLanguages'        => array('fr'),
            ),
            array(
               'forwardToReplicas' => true,
            ),
        );

        parent::__construct($post_type, $index_name, $algolia_client, $index_config);
    }

    public function searchableAttributes()
    {
        return array_merge($this->acf_fields, $this->taxonomies);
    }

}
