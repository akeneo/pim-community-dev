<?php


namespace Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Command;


use Akeneo\Pim\Automation\FranklinInsights\Application\Converter\FranklinAttributeLabelToAttributeCodeInterface;
use Akeneo\Pim\Automation\FranklinInsights\Application\Converter\FranklinAttributeLabelToAttributeLabelInterface;
use Akeneo\Pim\Automation\FranklinInsights\Application\Converter\FranklinAttributeTypeToAttributeTypeInterface;
use Akeneo\Pim\Automation\FranklinInsights\Application\DataProvider\PimAttributeCodeGeneratorInterface;
use Akeneo\Pim\Automation\FranklinInsights\Application\DataProvider\PimAttributeGroupFactoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Service\CreateAttributeInterface;
use Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Service\UpdateFamilyInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Write\AttributeMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeLabel;
use Akeneo\Pim\Structure\Component\AttributeTypes;

class CreateAttributeInFamilyHandler
{
    /** @var CreateAttributeInterface */
    private $createAttribute;

    /** @var UpdateFamilyInterface */
    private $updateFamily;

    /**
     * @param CreateAttributeInterface $createAttribute
     * @param UpdateFamilyInterface $updateFamily
     */
    public function __construct(
        CreateAttributeInterface $createAttribute,
        UpdateFamilyInterface $updateFamily
    ) {
        $this->createAttribute = $createAttribute;
        $this->updateFamily = $updateFamily;
    }

    public function handle(CreateAttributeInFamilyCommand $command)
    {
        $pimFamilyCode = $command->getPimFamilyCode();
        $franklinAttributeLabel = $command->getFranklinAttributeLabel();
        $franklinAttributeType = $command->getFranklinAttributeType();

        // @todo[DAPI-216] match FRANKLIN attribute type with PIM attribute type OR throw exception
        $availableTypes = array_unique(array_values(AttributeMapping::AUTHORIZED_ATTRIBUTE_TYPE_MAPPINGS));

        if (!in_array($franklinAttributeType, $availableTypes)) {
            throw new \InvalidArgumentException(
                sprintf('Franklin attribute type is not valid "%s". Allowed values [%s]',
                    $franklinAttributeType,
                    implode(', ', $availableTypes)
                )
            );
        }

        $pimAttributeGroupCode = 'franklin';
        $pimAttributeCode = AttributeCode::fromString($franklinAttributeLabel);
        $pimAttributeLabel = new AttributeLabel((string) $franklinAttributeLabel);
        $pimAttributeType = AttributeTypes::TEXT;

        // @todo[DAPI-216] create PIM attribute with generated PIM attribute code and PIM attribute label
        $this->createAttribute->create($pimAttributeCode, $pimAttributeLabel, $pimAttributeType, $pimAttributeGroupCode);

        // @todo[DAPI-216] add new PIM attribute to PIM family. Do we have to take care of channels ?
        $this->updateFamily->addAttributeToFamily($pimAttributeCode, $pimFamilyCode);
    }
}
