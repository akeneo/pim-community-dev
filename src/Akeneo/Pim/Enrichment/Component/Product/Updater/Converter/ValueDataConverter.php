<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Updater\Converter;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * Converts data from a source value in order to be able to instantiate another value with another attribute type
 */
interface ValueDataConverter
{
    public function supportsAttributes(AttributeInterface $sourceAttribute, AttributeInterface $targetAttribute): bool;

    /**
     * @param ValueInterface $sourceValue
     * @param AttributeInterface $targetAttribute
     *
     * @return mixed data in the expected format for the target attribute's value factory
     */
    public function convert(ValueInterface $sourceValue, AttributeInterface $targetAttribute);
}
