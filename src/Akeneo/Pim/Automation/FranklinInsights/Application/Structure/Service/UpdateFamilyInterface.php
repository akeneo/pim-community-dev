<?php


namespace Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Service;


use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;

interface UpdateFamilyInterface
{
    public function addAttributeToFamily(AttributeCode $attributeCode, FamilyCode $familyCode);
}