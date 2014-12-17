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
        'proposals' => 'Proposal index',
    ];

    /**
     * {@inheritdoc}
     */
    public function getPageMapping()
    {
        return array_merge($this->pageMapping, $this->enterprisePageMapping);
    }

    /**
     * @Given /^I should be on the published index page$/
     */
    public function iShouldBeOnThePublishedProductsPage()
    {
        $expectedAddress = $this->getPage('Published index')->getUrl();
        $this->assertAddress($expectedAddress);
    }
}
