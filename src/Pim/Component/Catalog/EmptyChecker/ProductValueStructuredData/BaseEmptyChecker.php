<?php

namespace Pim\Component\Catalog\EmptyChecker\ProductValueStructuredData;

/**
 * Base empty product value data checker which supports all native attribute types
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BaseEmptyChecker implements EmptyCheckerInterface
{
    /**
     * {@inheritdoc}
     */
    public function isEmpty($attributeCode, $valueData)
    {
        if ('' === $valueData || [] === $valueData || null === $valueData) {
            return true;
        }

        if (
            is_array($valueData) && array_key_exists('unit', $valueData) && array_key_exists('data', $valueData)
            && null === $valueData['data']
        ) {
            return true;
        }

        if (
            is_array($valueData) && array_key_exists('filePath', $valueData)
            && array_key_exists('originalFilename', $valueData) && null === $valueData['filePath']
        ) {
            return true;
        }

        if (is_array($valueData) && count($valueData) > 0 && isset($valueData[0]) && is_array($valueData[0])
            && array_key_exists('currency', $valueData[0])
        ) {
            foreach ($valueData as $price) {
                if (null !== $price['data']) {
                    return false;
                }
            }
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($attributeCode)
    {
        return true;
    }
}
