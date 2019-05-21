<?php


namespace Akeneo\Pim\Automation\FranklinInsights\Application\DataProvider;


use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;

interface PimAttributeCodeGeneratorInterface
{
    public function generate(array $parameters = []): AttributeCode;
}