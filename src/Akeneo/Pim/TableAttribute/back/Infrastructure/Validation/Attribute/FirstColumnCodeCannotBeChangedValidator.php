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
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class FirstColumnCodeCannotBeChangedValidator extends ConstraintValidator
{
    private AttributeRepositoryInterface $attributeRepository;

    public function __construct(AttributeRepositoryInterface $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    public function validate($attribute, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, FirstColumnCodeCannotBeChanged::class);
        Assert::isInstanceOf($attribute, AttributeInterface::class);

        $newRawTableConfiguration = $attribute->getRawTableConfiguration();
        if (!\is_array($newRawTableConfiguration) || 0 === count($newRawTableConfiguration)) {
            return;
        }

        /** @var AttributeInterface $formerAttribute */
        $formerAttribute = $this->attributeRepository->findOneByIdentifier($attribute->getCode());
        if (!$formerAttribute instanceof AttributeInterface) {
            return;
        }

        $formerRawTableConfiguration = $formerAttribute->getRawTableConfiguration();
        if (!\is_array($formerRawTableConfiguration) || 0 === count($formerRawTableConfiguration)) {
            return;
        }

        $formerFirstColumnCode = $formerRawTableConfiguration[0]['code'] ?? null;
        $newFirstColumnCode = $newRawTableConfiguration[0]['code'] ?? null;
        if (!\is_string($formerFirstColumnCode) || !\is_string($newFirstColumnCode)) {
            return;
        }

        $formerFirstColumnCode = ColumnCode::fromString($formerFirstColumnCode);
        $newFirstColumnCode = ColumnCode::fromString($newFirstColumnCode);
        if (!$formerFirstColumnCode->equals($newFirstColumnCode)) {
            $this->context->buildViolation($constraint->message)
                ->atPath('[0].code')
                ->addViolation();
        }
    }
}
