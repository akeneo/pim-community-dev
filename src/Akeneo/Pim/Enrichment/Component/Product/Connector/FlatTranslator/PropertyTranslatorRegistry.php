<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator;

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\PropertyTranslator\PropertyFlatTranslator;
use Webmozart\Assert\Assert;

class PropertyTranslatorRegistry
{
    /**
     * @var array
     */
    private $translators;

    public function __construct(array $translators)
    {
        Assert::allImplementsInterface($translators, PropertyFlatTranslator::class);
        $this->translators = $translators;
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
