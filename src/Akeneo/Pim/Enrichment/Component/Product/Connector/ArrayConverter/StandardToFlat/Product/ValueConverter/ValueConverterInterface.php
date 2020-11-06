<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

/**
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface ValueConverterInterface
{
    /**
     * Does the converter supports the attribute
     *
     * @param AttributeInterface $attribute
     */
    public function supportsAttribute(AttributeInterface $attribute): bool;

    /**
     * Converts a value
     *
     * @param string $attributeCode
     * @param mixed  $data
     */
    public function convert(string $attributeCode, $data): array;
}
