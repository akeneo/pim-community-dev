<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator;

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\AttributeFlatTranslator\AttributeFlatTranslator;
use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\PropertyTranslator\PropertyFlatTranslator;

class TranslatorRegistry
{
    /**
     * @var array
     */
    private $translators;

    public function __construct(array $translators)
    {
        $this->translators = $translators;
    }

    /**
     * @param $column
     * @return AttributeFlatTranslator|PropertyFlatTranslator|null
     */
    public function getTranslator($column)
    {
        foreach ($this->translators as $translator) {
            if ($translator->support($column)) {
                return $translator;
            }
        }

        return null;
    }
}
