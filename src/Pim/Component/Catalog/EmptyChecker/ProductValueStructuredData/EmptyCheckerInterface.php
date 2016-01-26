<?php

namespace Pim\Component\Catalog\EmptyChecker\ProductValueStructuredData;

/**
 * Determines whether the structured data of a product value is empty depending on its attribute code
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface EmptyCheckerInterface
{
    /**
     * Check if the structured product value data is empty
     *
     * @param string $attributeCode
     * @param mixed  $valueData
     *
     * @return bool the data is empty
     */
    public function isEmpty($attributeCode, $valueData);

    /**
     * Supports the attribute
     *
     * @param string $attributeCode
     *
     * @return bool
     */
    public function supports($attributeCode);
}
