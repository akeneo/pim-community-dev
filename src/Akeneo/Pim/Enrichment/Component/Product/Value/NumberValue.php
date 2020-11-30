<?php


namespace Akeneo\Pim\Enrichment\Component\Product\Value;


class NumberValue extends ScalarValue
{
    public function __construct(string $attributeCode, int $data = null, ?string $scopeCode, ?string $localeCode)
    {
        parent::__construct($attributeCode, $data, $scopeCode, $localeCode);
    }

}
