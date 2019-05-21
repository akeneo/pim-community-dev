<?php


namespace Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Service;


use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeLabel;

interface CreateAttributeInterface
{
    public function create(AttributeCode $attributeCode, AttributeLabel $attributeLabel, string $attributeType, string $attributeGroupCode);
}