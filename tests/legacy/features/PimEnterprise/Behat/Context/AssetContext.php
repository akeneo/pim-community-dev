<?php

namespace PimEnterprise\Behat\Context;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Context\PimContext;

class AssetContext extends PimContext
{
    use SpinCapableTrait;

    /**
     * @param string $asset
     *
     * @throws \Context\Spin\TimeoutException
     *
     * @When /^I click on the "([^"]+)" asset thumbnail$/
     */
    public function iClickOnTheAssetThumbnail($asset)
    {
        $this->spin(function () use ($asset) {
            return $this->getCurrentPage()->find('css', sprintf('.asset-thumbnail-item[data-asset=%s]', $asset));
        }, sprintf('Cannot find the asset thumbnail %s', $asset))->click();
    }

    /**
     * @param $side
     *
     * @throws \Context\Spin\TimeoutException
     *
     * @When /^I navigate to the (left|right) in the asset collection preview$/
     */
    public function iNavigateInTheAssetCollectionPreview($side)
    {
        $this->spin(function () use ($side) {
            return $this->getCurrentPage()->find('css', sprintf('.browse-%s', $side));
        }, sprintf('Can not find the asset collection preview navigation to %s', $side))->click();
    }

    /**
     * @throws \Context\Spin\TimeoutException
     *
     * @When /^I close the asset collection preview$/
     */
    public function iCloseTheAssetCollectionPreview()
    {
        $this->spin(function () {
            return $this->getCurrentPage()->find('css', '.cancel');
        }, 'Can not close the asset collection preview')->click();
    }
}
