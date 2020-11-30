<?php


namespace Akeneo\Pim\Enrichment\Component\Product\Value;


class TextValue extends ScalarValue
{
    public function __construct(string $attributeCode, string $data = null, ?string $scopeCode, ?string $localeCode)
    {
        parent::__construct($attributeCode, $data, $scopeCode, $localeCode);
    }

}
