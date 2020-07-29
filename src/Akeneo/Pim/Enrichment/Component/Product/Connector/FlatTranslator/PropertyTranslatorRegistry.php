<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator;

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\PropertyTranslator\PropertyFlatTranslator;
use Webmozart\Assert\Assert;

class PropertyTranslatorRegistry
{
    /**
     * @var array
     */
    private $translators = [];

    public function addTranslator(PropertyFlatTranslator $propertyFlatTranslator): void
    {
        $this->translators[] = $propertyFlatTranslator;
    }

    public function getTranslator(string $column): ?PropertyFlatTranslator
    {
        foreach ($this->translators as $translator) {
            if ($translator->support($column)) {
                return $translator;
            }
        }

        return null;
    }
}
