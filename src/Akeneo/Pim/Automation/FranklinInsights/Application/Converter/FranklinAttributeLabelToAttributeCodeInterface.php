<?php


namespace Akeneo\Pim\Automation\FranklinInsights\Application\Converter;


use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FranklinAttributeLabel;

interface FranklinAttributeLabelToAttributeCodeInterface
{
    public function convert(FranklinAttributeLabel $franklinAttributeLabel): AttributeCode;
}