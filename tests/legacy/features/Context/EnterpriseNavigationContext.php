<?php

namespace Context;

use Behat\ChainedStepsExtension\Step\Then;
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
        'published products' => 'PublishedProduct index',
        'Mass_upload show'   => 'MassUpload show',
        'proposals'          => 'Proposal index',
        'rules'              => 'Rule index',
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
        $expectedAddress = $this->getPage('PublishedProduct index')->getUrl();
        $this->assertAddress($expectedAddress);
    }

    /**
     * @Given /^I should be on the proposals index page$/
     */
    public function iShouldBeOnTheProposalsPage()
    {
        $expectedAddress = $this->getPage('Proposal index')->getUrl();
        $this->assertAddress($expectedAddress);
    }
}
