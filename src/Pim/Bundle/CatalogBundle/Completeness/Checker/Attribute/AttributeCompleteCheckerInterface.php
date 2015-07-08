<?php

namespace Pim\Bundle\CatalogBundle\Completeness\Checker\Attribute;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface AttributeCompleteCheckerInterface
{
    /**
     * @param ProductValueInterface $productValue
     * @param ChannelInterface|null $channel
     * @param string|null           $localeCode
     *
     * @return bool
     */
    public function isComplete(ProductValueInterface $productValue, ChannelInterface $channel, $localeCode = null);

    /**
     * @param AttributeInterface $attribute
     *
     * @return bool
     */
    public function supportsAttribute(AttributeInterface $attribute);
}
