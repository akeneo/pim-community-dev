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

namespace Akeneo\Pim\TableAttribute\Infrastructure\Validation\ProductValue;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Infrastructure\Value\TableValue;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class FirstColumnShouldBeFilledValidator extends ConstraintValidator
{
    private TableConfigurationRepository $tableConfigurationRepository;

    public function __construct(TableConfigurationRepository $tableConfigurationRepository)
    {
        $this->tableConfigurationRepository = $tableConfigurationRepository;
    }

    public function validate($tableValue, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, FirstColumnShouldBeFilled::class);
        if (!$tableValue instanceof TableValue) {
            return;
        }

        $tableConfiguration = $this->tableConfigurationRepository->getByAttributeCode($tableValue->getAttributeCode());
        $firstColumnId = $tableConfiguration->getFirstColumnId();
        $firstColumnCode = $tableConfiguration->getFirstColumnCode();

        foreach ($tableValue->getData() as $rowIndex => $row) {
            if (null === $row->cell($firstColumnId)) {
                $this->context
                    ->buildViolation($constraint->message, ['{{ columnCode }}' => $firstColumnCode->asString()])
                    ->atPath(sprintf('[%d]', $rowIndex))
                    ->addViolation();
            }
        }
    }
}
