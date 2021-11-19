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
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationNotFoundException;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class FirstColumnCodeCannotBeChangedValidator extends ConstraintValidator
{
    private TableConfigurationRepository $tableConfigurationRepository;

    public function __construct(TableConfigurationRepository $tableConfigurationRepository)
    {
        $this->tableConfigurationRepository = $tableConfigurationRepository;
    }

    public function validate($attribute, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, FirstColumnCodeCannotBeChanged::class);
        Assert::isInstanceOf($attribute, AttributeInterface::class);

        $newRawTableConfiguration = $attribute->getRawTableConfiguration();
        if (!\is_array($newRawTableConfiguration) || [] === $newRawTableConfiguration) {
            return;
        }

        $newFirstStringColumnCode = $newRawTableConfiguration[0]['code'] ?? null;
        if (!\is_string($newFirstStringColumnCode)) {
            return;
        }

        try {
            $newFirstColumnCode = ColumnCode::fromString($newFirstStringColumnCode);
        } catch (\Exception $e) {
            return;
        }

        try {
            $formerTableConfiguration = $this->tableConfigurationRepository->getByAttributeCode($attribute->getCode());
        } catch (TableConfigurationNotFoundException $e) {
            return;
        }

        $formerFirstColumnCode = $formerTableConfiguration->getFirstColumnCode();
        if (!$formerFirstColumnCode->equals($newFirstColumnCode)) {
            $this->context->buildViolation($constraint->message)
                ->atPath('[0].code')
                ->addViolation();
        }
    }
}
