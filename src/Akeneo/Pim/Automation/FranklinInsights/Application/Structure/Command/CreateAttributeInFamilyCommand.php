<?php


namespace Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Command;


use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FranklinAttributeLabel;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FranklinAttributeType;

class CreateAttributeInFamilyCommand
{
    /**
     * @var FamilyCode
     */
    private $pimFamilyCode;
    /**
     * @var FranklinAttributeLabel
     */
    private $franklinAttributeLabel;
    /**
     * @var string
     */
    private $franklinAttributeType;

    /** @var AttributeCode */
    private $createdAttributeCode;

    /**
     * CreateAttributeInFamilyCommand constructor.
     * @param FamilyCode $pimFamilyCode
     * @param FranklinAttributeLabel $franklinAttributeLabel
     * @param FranklinAttributeType $franklinAttributeType
     */
    public function __construct(FamilyCode $pimFamilyCode, FranklinAttributeLabel $franklinAttributeLabel, FranklinAttributeType $franklinAttributeType)
    {
        $this->pimFamilyCode = $pimFamilyCode;
        $this->franklinAttributeLabel = $franklinAttributeLabel;
        $this->franklinAttributeType = $franklinAttributeType;
    }

    /**
     * @return FamilyCode
     */
    public function getPimFamilyCode(): FamilyCode
    {
        return $this->pimFamilyCode;
    }

    /**
     * @return FranklinAttributeLabel
     */
    public function getFranklinAttributeLabel(): FranklinAttributeLabel
    {
        return $this->franklinAttributeLabel;
    }
    /**
     * @return string
     */
    public function getFranklinAttributeType(): FranklinAttributeType
    {
        return $this->franklinAttributeType;
    }

    /**
     * @return AttributeCode
     */
    public function getCreatedAttributeCode(): AttributeCode
    {
        return $this->createdAttributeCode;
    }

    /**
     * @param AttributeCode $createdAttributeCode
     */
    public function setCreatedAttributeCode(AttributeCode $createdAttributeCode): void
    {
        $this->createdAttributeCode = $createdAttributeCode;
    }
}
