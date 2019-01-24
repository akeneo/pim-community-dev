<?php

namespace Context;

use Akeneo\Asset\Component\Model\Category;
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
        'assets'             => 'Asset index',
        'asset edit'         => 'Asset edit',
        'asset mass upload'  => 'Asset massUpload',
        'assets categories'  => 'Asset Category tree index',
        'Franklin configuration' => 'FranklinInsights configuration index',
        'Franklin identifiers mapping' => 'FranklinInsights mapping identifiers index'
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

    /**
     * @param string $assetCode
     *
     * @Given /^I should be on the "([^"]+)" asset edit page$/
     */
    public function iShouldBeOnTheAssetEditPage($assetCode)
    {
        $asset = $this->getMainContext()->getSubcontext('fixtures')->getAsset($assetCode);
        $expectedAddress = $this->getPage('Asset edit')->getUrl(['id' => $asset->getId()]);

        $this->assertAddress($expectedAddress);
        $this->currentPage = 'Asset edit';
    }

    /**
     * @Given /^I should be on the assets page$/
     */
    public function iShouldBeOnTheAssetsPage()
    {
        $expectedAddress = $this->getPage('Asset index')->getUrl();
        $this->assertAddress($expectedAddress);
        $this->currentPage = 'Asset index';
    }

    /**
     * @param string $identifier
     *
     * @Given /^I edit the "([^"]*)" asset category$/
     * @Given /^I am on the "([^"]*)" asset category page$/
     */
    public function iEditTheAssetCategory($identifier)
    {
        $page   = 'AssetCategory';
        $getter = sprintf('get%s', $page);
        $entity = $this->getFixturesContext()->$getter($identifier);
        $this->openPage(sprintf('Asset Category edit', $page), ['id' => $entity->getId()]);

        return new Then('I should see the text "edit "');
    }

    /**
     * @param Category $category
     *
     * @Given /^I am on the (asset category "([^"]*)") node creation page$/
     */
    public function iAmOnTheAssetCategoryNodeCreationPage(Category $category)
    {
        $this->openPage('Asset Category node creation', ['id' => $category->getId()]);
    }

    /**
     * @param Category $category
     *
     * @Then /^I should be on the (asset category "([^"]*)") edit page$/
     */
    public function iShouldBeOnTheAssetCategoryEditPage(Category $category)
    {
        $expectedAddress = $this->getPage('Asset Category edit')->getUrl(['id' => $category->getId()]);
        $this->assertAddress($expectedAddress);
    }
}
