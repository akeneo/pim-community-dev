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

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ReferenceEntityColumn;
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

        if (!\is_array($value)) {
            return;
        }

        $columnCode = $value['code'] ?? null;
        $dataType = $value['data_type'] ?? null;
        $referenceEntityIdentifier = $value['reference_entity_identifier'] ?? null;
        if (null === $columnCode || !is_string($referenceEntityIdentifier) || ReferenceEntityColumn::DATATYPE !== $dataType) {
            return;
        }

        $attribute = $this->context->getRoot();
        if (!$attribute instanceof AttributeInterface || !\is_string($attribute->getCode())) {
            return;
        }

        try {
            $formerTableConfiguration = $this->tableConfigurationRepository->getByAttributeCode($attribute->getCode());
        } catch (TableConfigurationNotFoundException) {
            return;
        }

        $formerColumnDefinition = $formerTableConfiguration->getColumnByCode(ColumnCode::fromString($columnCode));
        if (!$formerColumnDefinition instanceof ReferenceEntityColumn) {
            return;
        }
        try {
            $newReferenceEntityIdentifier = ReferenceEntityIdentifier::fromString($referenceEntityIdentifier);
        } catch (\InvalidArgumentException) {
            return;
        }

        if (!$newReferenceEntityIdentifier->equals($formerColumnDefinition->referenceEntityIdentifier())) {
            $this->context
                ->buildViolation('pim_table_configuration.validation.table_configuration.reference_entity_identifier_is_immutable')
                ->atPath('reference_entity_identifier')
                ->addViolation();
        }
    }
}
