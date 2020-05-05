<?php

/**
 * This file is part of WpAlgolia plugin.
 * (c) Antoine Girard for Mill3 Studio <antoine@mill3.studio>
 * @version 0.0.7
 */

namespace WpAlgolia\Register;

use WpAlgolia\RegisterAbstract as WpAlgoliaRegisterAbstract;
use WpAlgolia\RegisterInterface as WpAlgoliaRegisterInterface;

class Pages extends WpAlgoliaRegisterAbstract implements WpAlgoliaRegisterInterface
{
    public $searchable_fields = array('post_title', 'content', 'content_rows');

    public $acf_fields = array();

    public $taxonomies = array();

    public function __construct($post_type, $index_name, $algolia_client)
    {
        $index_config = array(
            'acf_fields'        => $this->acf_fields,
            'taxonomies'        => $this->taxonomies,
            'post_type'         => $post_type,
            'hidden_flag_field' => 'search_hidden',
            'config'            => array(
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
        return array_merge($this->searchable_fields, $this->acf_fields, $this->taxonomies);
    }

    // implement any special data handling for post type here
    public function extraFields($data, $postID, $instance) {

        $content_rows = get_field('content_rows', $postID);
        if(!\is_array($content_rows)) return $data;

        // array holder for each content rows
        $data['content_rows'] = [];

        // index a bunch of content-rows
        foreach ($content_rows as $key => $row) {
            $layout = $row['acf_fc_layout'];
            $row_data = isset($row[$layout]) ? $row[$layout] : null;

            if (isset($row_data['text'])) {
                array_push($data['content_rows'], $instance->cleanText($row_data['text']));
            }

            if (isset($row_data['texte'])) {
                array_push($data['content_rows'], $instance->cleanText($row_data['texte']));
            }

            if ( isset($row_data['faqs']) && \is_array($row_data['faqs']) ) {
                foreach ($row_data['faqs'] as $faq) {
                    array_push($data['content_rows'], $instance->cleanText($faq->post_title));
                    array_push($data['content_rows'], $instance->cleanText($faq->post_content));
                }
            }

            if (isset($row_data['text_columns']) && \is_array($row_data['text_columns'])) {
                foreach ($row_data['text_columns'] as $text_column) {
                    array_push($data['content_rows'], $instance->cleanText($text_column['content']));
                }
            }
        }

        // merge all text in a single long string
        $data['content_rows'] = $instance->prepareTextContent( implode( $data['content_rows'], ', ') );

        return $data;
    }
}
