<?php


namespace Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Command;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FranklinAttributeLabel;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FranklinAttributeType;

class CreateAttributeInFamilyCommand
{
    private $pimFamilyCode;
    private $pimAttributeCode;
    private $franklinAttributeLabel;
    private $franklinAttributeType;

    public function __construct(
        FamilyCode $pimFamilyCode,
        AttributeCode $pimAttributeCode,
        FranklinAttributeLabel $franklinAttributeLabel,
        FranklinAttributeType $franklinAttributeType
    ) {
        $this->pimFamilyCode = $pimFamilyCode;
        $this->pimAttributeCode = $pimAttributeCode;
        $this->franklinAttributeLabel = $franklinAttributeLabel;
        $this->franklinAttributeType = $franklinAttributeType;
    }

    public function getPimFamilyCode(): FamilyCode
    {
        return $this->pimFamilyCode;
    }

    public function getPimAttributeCode(): AttributeCode
    {
        return $this->pimAttributeCode;
    }

    public function getFranklinAttributeLabel(): FranklinAttributeLabel
    {
        return $this->franklinAttributeLabel;
    }

    public function getFranklinAttributeType(): FranklinAttributeType
    {
        return $this->franklinAttributeType;
    }
}
