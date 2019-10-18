<?php

/**
 * This file is part of WpAlgolia plugin.
 * (c) Antoine Girard for Mill3 Studio <antoine@mill3.studio>
 * @version 0.0.1
 * @since 0.0.1
 */

namespace WpAlgolia\Register;

use WpAlgolia\Register\RegisterAbstract as WpAlgoliaRegisterAbstract;
use WpAlgolia\Register\RegisterInterface as WpAlgoliaRegisterInterface;

class Programs extends WpAlgoliaRegisterAbstract implements WpAlgoliaRegisterInterface
{
    public function __construct($post_type, $index_name)
    {
        parent::__construct($post_type, $index_name);
    }
}
