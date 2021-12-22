<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\RecordColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationNotFoundException;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ReferenceEntityIdentifier;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class ImmutableReferenceEntityIdentifierValidator extends ConstraintValidator
{
    public function __construct(private TableConfigurationRepository $tableConfigurationRepository)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, ImmutableReferenceEntityIdentifier::class);

        if (!$value instanceof AttributeInterface || $value->getType() !== AttributeTypes::TABLE) {
            return;
        }
        if (null === $value->getRawTableConfiguration() || !\is_string($value->getCode())) {
            return;
        }

        $rawTableConfiguration = $value->getRawTableConfiguration();
        try {
            $formerTableConfiguration = $this->tableConfigurationRepository->getByAttributeCode($value->getCode());
        } catch (TableConfigurationNotFoundException) {
            return;
        }

        foreach ($rawTableConfiguration as $index => $newColumnDefinition) {
            $columnCode = $newColumnDefinition['code'] ?? null;
            $dataType = $newColumnDefinition['data_type'] ?? null;
            if (null === $columnCode || RecordColumn::DATATYPE !== $dataType) {
                continue;
            }
            $formerColumnDefinition = $formerTableConfiguration->getColumnByCode(ColumnCode::fromString($columnCode));
            if (!$formerColumnDefinition instanceof RecordColumn) {
                continue;
            }
            try {
                $newReferenceEntityIdentifier = ReferenceEntityIdentifier::fromString(
                    $newColumnDefinition['reference_entity_identifier'] ?? null
                );
            } catch (\InvalidArgumentException) {
                continue;
            }

            if (!$newReferenceEntityIdentifier->equals($formerColumnDefinition->referenceEntityIdentifier())) {
                $this->context
                    ->buildViolation('pim_table_configuration.validation.table_configuration.reference_entity_identifier_is_immutable')
                    ->atPath(\sprintf('table_configuration[%d].reference_entity_identifier', $index))
                    ->addViolation();
            }
        }
    }
}
