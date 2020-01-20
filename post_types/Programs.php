<?php

/**
 * This file is part of WpAlgolia plugin.
 * (c) Antoine Girard for Mill3 Studio <antoine@mill3.studio>
 * @version 0.0.7
 */

namespace WpAlgolia\Register;

use WpAlgolia\RegisterAbstract as WpAlgoliaRegisterAbstract;
use WpAlgolia\RegisterInterface as WpAlgoliaRegisterInterface;

class Programs extends WpAlgoliaRegisterAbstract implements WpAlgoliaRegisterInterface
{
    public $searchable_fields = array('post_title');

    public $acf_fields = array('code', 'brochure', 'title', 'subtitle', 'body', 'why_title', 'why_subtitle', 'perspectives_title', 'perspectives_subtitle', 'verify_title');

    public $taxonomies = array('duration', 'programs_type', 'profile', 'condition', 'period');

    public function __construct($post_type, $index_name, $algolia_client)
    {
        $index_config = array(
            'acf_fields'        => $this->acf_fields,
            'taxonomies'        => $this->taxonomies,
            'post_type'         => $post_type,
            'hidden_flag_field' => 'search_hidden',
            'config'            => array(
                'searchableAttributes'  => $this->searchableAttributes(),
                'customRanking'         => array('asc(code)'),
                'attributesForFaceting' => array('searchable(programs_type)', 'searchable(profile)', 'searchable(condition)', 'searchable(duration)'),
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
        return array_merge($this->searchable_fields, $this->acf_fields, $this->taxonomies);
    }
}
