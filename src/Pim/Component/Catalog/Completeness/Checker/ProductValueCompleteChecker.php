<?php

namespace Pim\Component\Catalog\Completeness\Checker;

use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueCompleteChecker
{
    /** @var CompleteCheckerRegistryInterface */
    protected $attributeCheckerRegistry;

    /**
     * @param CompleteCheckerRegistryInterface $attributeCompleteCheckerRegistry
     */
    public function __construct(CompleteCheckerRegistryInterface $attributeCompleteCheckerRegistry)
    {
        $this->attributeCheckerRegistry = $attributeCompleteCheckerRegistry;
    }

    /**
     * @param ProductValueInterface $value
     * @param ChannelInterface      $channel
     * @param string                $localeCode
     *
     * @return bool
     */
    public function isComplete(ProductValueInterface $value, ChannelInterface $channel, $localeCode)
    {
        if ($value === null) {
            return false;
        }

        $data = $value->getData();

        if (null === $data || '' === $data || [] === $data
            || ($data instanceof \Countable && count($data) === 0)
        ) {
            return false;
        }

        foreach ($this->attributeCheckerRegistry->getAttributeCheckers() as $attributeChecker) {
            if ($attributeChecker->supportsAttribute($value->getAttribute())
                && !$attributeChecker->isComplete($value, $channel, $localeCode)
            ) {
                return false;
            }
        }

        return true;
    }
}
