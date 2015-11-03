<?php

namespace Pim\Component\Catalog\Completeness\Checker;

use Pim\Component\Catalog\ChannelInterface;
use Pim\Component\Catalog\LocaleInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductValueCompleteCheckerInterface
{
    /**
     * @param \Pim\Component\Catalog\Model\ProductValueInterface $productValue
     * @param \Pim\Component\Catalog\ChannelInterface|null $channel
     * @param LocaleInterface|null  $locale
     *
     * @return bool
     */
    public function isComplete(
        ProductValueInterface $productValue,
        ChannelInterface $channel = null,
        LocaleInterface $locale = null
    );

    /**
     * @param \Pim\Component\Catalog\Model\ProductValueInterface $productValue
     *
     * @return bool
     */
    public function supportsValue(ProductValueInterface $productValue);
}
