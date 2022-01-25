<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute\Measurement;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Query\IsMeasurementFamilyLinkedToATableColumn;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

class MeasurementFamilyShouldNotBeLinkedToATableColumnValidator extends ConstraintValidator
{
    public function __construct(private IsMeasurementFamilyLinkedToATableColumn $isMeasurementFamilyLinkedToATableColumn)
    {
    }

    public function validate($deleteMeasurementFamilyCommand, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, MeasurementFamilyShouldNotBeLinkedToATableColumn::class);
        /** @phpstan-ignore-next-line */
        Assert::isInstanceOf($deleteMeasurementFamilyCommand, 'Akeneo\Tool\Bundle\MeasureBundle\Application\DeleteMeasurementFamily\DeleteMeasurementFamilyCommand');

        /** @phpstan-ignore-next-line */
        $code = $deleteMeasurementFamilyCommand->code;

        if ($this->isMeasurementFamilyLinkedToATableColumn->forCode($code)) {
            $this->context
                ->buildViolation($constraint->message, ['{{ measurement_family_code }}' => $code])
                ->addViolation();
        }
    }
}
