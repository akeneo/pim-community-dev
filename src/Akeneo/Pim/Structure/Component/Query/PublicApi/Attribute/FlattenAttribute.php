<?php

namespace Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute;

class FlattenAttribute
{
    private string $code;
    private string $label;
    private string $attributeGroupCode;
    private string $attributeGroupLabel;

    public function __construct(string $code, string $label, string $attributeGroupCode, string $attributeGroupLabel)
    {
        $this->code = $code;
        $this->label = $label;
        $this->attributeGroupCode = $attributeGroupCode;
        $this->attributeGroupLabel = $attributeGroupLabel;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getAttributeGroupCode(): string
    {
        return $this->attributeGroupCode;
    }

    public function getAttributeGroupLabel(): string
    {
        return $this->attributeGroupLabel;
    }
}
