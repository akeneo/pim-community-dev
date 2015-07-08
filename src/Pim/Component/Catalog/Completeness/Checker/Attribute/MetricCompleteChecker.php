<?php

namespace Pim\Component\Catalog\Completeness\Checker\Attribute;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricCompleteChecker implements AttributeCompleteCheckerInterface
{
    public function isComplete(ProductValueInterface $value, ChannelInterface $channel, $localeCode = null)
    {
        $metric = $value->getMetric();

        if (!$metric || null === $metric->getData()) {
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
        return 'pim_catalog_metric' === $attribute->getAttributeType();
    }
}
