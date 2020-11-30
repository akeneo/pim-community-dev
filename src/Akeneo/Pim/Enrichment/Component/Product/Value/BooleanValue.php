<?php


namespace Akeneo\Pim\Enrichment\Component\Product\Value;


class BooleanValue extends ScalarValue
{
    public function __construct(string $attributeCode, bool $data = null, ?string $scopeCode, ?string $localeCode)
    {
        parent::__construct($attributeCode, $data, $scopeCode, $localeCode);
    }
}
