<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Update;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception\UndefinedAttributeException;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception\UnexpectedAttributeTypeException;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Get\GetNomenclatureCommand;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Get\GetNomenclatureHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Validation\CommandValidatorInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\NomenclatureDefinition;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FamilyProperty;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\FamilyNomenclatureRepository;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\ReferenceEntityNomenclatureRepository;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\SimpleSelectNomenclatureRepository;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UpdateNomenclatureHandler
{
    public function __construct(
        private readonly FamilyNomenclatureRepository $familyNomenclatureRepository,
        private readonly SimpleSelectNomenclatureRepository $simpleSelectNomenclatureRepository,
        private readonly CommandValidatorInterface $validator,
        private readonly GetNomenclatureHandler $getNomenclatureHandler,
        private readonly GetAttributes $getAttributes,
        private readonly ReferenceEntityNomenclatureRepository $referenceEntityNomenclatureRepository,
    ) {
    }

    public function __invoke(UpdateNomenclatureCommand $command): void
    {
        $this->validator->validate($command);

        $getNomenclatureCommand = new GetNomenclatureCommand($command->getPropertyCode());
        $normalizedNomenclature = ($this->getNomenclatureHandler)($getNomenclatureCommand);

        $nomenclatureDefinition = new NomenclatureDefinition(
            $normalizedNomenclature['operator'],
            $normalizedNomenclature['value'],
            $normalizedNomenclature['generate_if_empty'],
            $normalizedNomenclature['values']
        );

        Assert::notNull($command->getOperator());
        Assert::notNull($command->getValue());
        Assert::notNull($command->getGenerateIfEmpty());

        $nomenclatureDefinition = $nomenclatureDefinition
            ->withOperator($command->getOperator())
            ->withValue($command->getValue())
            ->withGenerateIfEmpty($command->getGenerateIfEmpty())
            ->withValues($command->getValues());

        if ($command->getPropertyCode() === FamilyProperty::TYPE) {
            $this->familyNomenclatureRepository->update($nomenclatureDefinition);
        } else {
            $attribute = $this->getAttributes->forCode($command->getPropertyCode());

            if (null === $attribute) {
                throw UndefinedAttributeException::withAttributeCode($command->getPropertyCode());
            }

            match ($attribute->type()) {
                AttributeTypes::OPTION_SIMPLE_SELECT => $this->simpleSelectNomenclatureRepository->update($command->getPropertyCode(), $nomenclatureDefinition),
                AttributeTypes::REFERENCE_ENTITY_SIMPLE_SELECT => $this->referenceEntityNomenclatureRepository->update($command->getPropertyCode(), $nomenclatureDefinition),
                default => throw UnexpectedAttributeTypeException::withAttributeCode($attribute->type(), $attribute->code())
            };
        }
    }
}
