<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator;

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\AttributeValue\FlatAttributeValueTranslatorInterface;

class AttributeValueRegistry
{
    /** @var FlatAttributeValueTranslatorInterface[] */
    private $translators = [];

    public function addTranslator(FlatAttributeValueTranslatorInterface $translator): void
    {
        $this->translators[] = $translator;
    }

    public function getTranslator(string $attributeType, string $columnName): ?FlatAttributeValueTranslatorInterface
    {
        foreach ($this->translators as $translator) {
            if ($translator->supports($attributeType, $columnName)) {
                return $translator;
            }
        }

        return null;
    }
}
