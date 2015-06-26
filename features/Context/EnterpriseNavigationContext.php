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
        'published'      => 'Published index',
        'proposals'      => 'Proposal index',
        'assets'         => 'Asset index',
        'asset edit'     => 'Asset edit'
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

    /**
     * @param string $asset
     *
     * @Given /^I should be on the "([^"]+)" asset edit page$/
     */
    public function iShouldBeOnTheAssetEditPage($assetCode)
    {
        $asset = $this->getMainContext()->getSubcontext('fixtures')->getProductAsset($assetCode);

        $expectedAddress = $this->getPage('ProductAsset edit')->getUrl(['id' => $asset->getId()]);
        $this->assertAddress($expectedAddress);
    }

    /**
     * @Given /^I should be on the assets page$/
     */
    public function iShouldBeOnTheAssetsPage()
    {
        $expectedAddress = $this->getPage('ProductAsset index')->getUrl();
        $this->assertAddress($expectedAddress);
    }
}
