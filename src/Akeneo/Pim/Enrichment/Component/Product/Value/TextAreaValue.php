<?php


namespace Akeneo\Pim\Enrichment\Component\Product\Value;


class TextAreaValue extends ScalarValue
{
    public function __construct(string $attributeCode, string $data = null, ?string $scopeCode, ?string $localeCode)
    {
        parent::__construct($attributeCode, $data, $scopeCode, $localeCode);
    }

}
