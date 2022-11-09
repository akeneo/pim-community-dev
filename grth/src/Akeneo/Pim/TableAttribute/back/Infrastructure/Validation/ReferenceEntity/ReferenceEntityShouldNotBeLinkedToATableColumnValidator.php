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

namespace Akeneo\Pim\TableAttribute\Infrastructure\Validation\ReferenceEntity;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Query\IsReferenceEntityLinkedToATableColumn;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class ReferenceEntityShouldNotBeLinkedToATableColumnValidator extends ConstraintValidator
{
    public function __construct(private IsReferenceEntityLinkedToATableColumn $isLinkedToATableColumn)
    {
    }

    public function validate($deleteReferenceEntityCommand, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, ReferenceEntityShouldNotBeLinkedToATableColumn::class);
        Assert::isInstanceOf($deleteReferenceEntityCommand, 'Akeneo\ReferenceEntity\Application\ReferenceEntity\DeleteReferenceEntity\DeleteReferenceEntityCommand');

        $identifier = $deleteReferenceEntityCommand->identifier;

        if ($this->isLinkedToATableColumn->forIdentifier($identifier)) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
