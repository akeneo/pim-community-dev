<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Updater\Converter;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CollectionToStringDataConverter implements ValueDataConverter
{
    private $supportedAttributeTypes = [
        AttributeTypes::OPTION_MULTI_SELECT => [
            AttributeTypes::TEXT => true,
            AttributeTypes::TEXTAREA => true,
        ],
        AttributeTypes::REFERENCE_ENTITY_COLLECTION => [
            AttributeTypes::TEXT => true,
            AttributeTypes::TEXTAREA => true,
        ],
        AttributeTypes::PRICE_COLLECTION => [
            AttributeTypes::TEXT => true,
            AttributeTypes::TEXTAREA => true,
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function supportsAttributes(AttributeInterface $sourceAttribute, AttributeInterface $targetAttribute): bool
    {
        return isset($this->supportedAttributeTypes[$sourceAttribute->getType()][$targetAttribute->getType()]);
    }

    /**
     * {@inheritdoc}
     */
    public function convert(ValueInterface $sourceValue, AttributeInterface $targetAttribute)
    {
        Assert::isIterable($sourceValue->getData());

        $chunks = [];
        foreach ($sourceValue->getData() as $data) {
            $chunks[] = (string) $data;
        }

        return implode(', ', $chunks);
    }
}
