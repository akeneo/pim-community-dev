<?php

namespace Context;

use Context\NavigationContext as BaseNavigationContext;

/**
 * Navigation context
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class EnterpriseNavigationContext extends BaseNavigationContext
{
    protected $enterprisePageMapping = [
        'published' => 'Published index',
    ];

    /**
     * {@inheritdoc}
     */
    public function getPageMapping()
    {
        return array_merge($this->pageMapping, $this->enterprisePageMapping);
    }
}
