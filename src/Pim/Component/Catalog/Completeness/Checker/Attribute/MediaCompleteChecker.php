<?php

namespace Pim\Component\Catalog\Completeness\Checker\Attribute;

use Pim\Bundle\CatalogBundle\AttributeType\AbstractAttributeType;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MediaCompleteChecker implements AttributeCompleteCheckerInterface
{
    public function isComplete(ProductValueInterface $value, ChannelInterface $channel, $localeCode = null)
    {
        $media = $value->getMedia();

        if (!$media || '' === $media->__toString()) {
            return false;
        }

        return true;
    }

    /**
     * @param AttributeInterface $attribute
     *
     * @return bool
     */
    public function supportsAttribute(AttributeInterface $attribute)
    {
        return AbstractAttributeType::BACKEND_TYPE_MEDIA === $attribute->getBackendType();
    }
}
