<?php

namespace Context;

use Behat\Mink\Exception\ExpectationException;
use Pim\Behat\Context\Domain\Enrich\ItemPickerContext;

/**
 * Overrides the ItemPickerContext to add the code related to asset picker
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class EnterpriseItemPickerContext extends ItemPickerContext
{
    /**
     * @Then /^the asset basket item "([^"]+)" should contain the thumbnail for channel "([^"]+)"(?: and locale "([^"]+)")?$/
     *
     * @param string      $code
     * @param string      $channelCode
     * @param string|null $localeCode
     *
     * @throws ExpectationException
     */
    public function theAssetBasketItemShouldContainThumbnailForContext($code, $channelCode, $localeCode = null)
    {
        $baksetItem = $this->getItemPickerBasketItems($code);
        $thumbnail  = $this->spin(function () use ($baksetItem) {
            return $baksetItem->find('css', '.AknGrid-fullImage');
        }, 'Impossible to find the thumbnail');

        $this
            ->getMainContext()
            ->getSubcontext('assertions')
            ->checkThumbnailUrlForContext($thumbnail, $code, $channelCode, $localeCode);
    }
}
