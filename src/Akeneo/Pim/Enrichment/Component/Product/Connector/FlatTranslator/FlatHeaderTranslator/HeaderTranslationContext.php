<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\FlatHeaderTranslator;

class HeaderTranslationContext
{
    /**
     * @var array
     */
    private $attributeTranslations;
    /**
     * @var array
     */
    private $associationTranslations;

    public function __construct(array $attributeTranslations, array $associationTranslations)
    {
        $this->attributeTranslations = $attributeTranslations;
        $this->associationTranslations = $associationTranslations;
    }

    public function getAssociationTranslation(string $associationTypeCode): ?string
    {
        return $this->associationTranslations[$associationTypeCode] ?? null;
    }

    public function getAttributeTranslation(string $attributeCode): ?string
    {
        return $this->attributeTranslations[$attributeCode] ?? null;
    }
}
