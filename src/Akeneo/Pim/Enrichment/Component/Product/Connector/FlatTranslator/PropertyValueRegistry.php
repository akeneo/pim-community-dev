<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator;

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\PropertyValue\FlatPropertyValueTranslatorInterface;

class PropertyValueRegistry
{
    /** @var FlatPropertyValueTranslatorInterface[] */
    private $translators = [];

    public function addTranslator(FlatPropertyValueTranslatorInterface $translator): void
    {
        $this->translators[] = $translator;
    }

    public function getTranslator(string $columnName): ?FlatPropertyValueTranslatorInterface
    {
        foreach ($this->translators as $translator) {
            if ($translator->supports($columnName)) {
                return $translator;
            }
        }

        return null;
    }
}
