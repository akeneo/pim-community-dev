<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\TailoredExportBundle\src\Infrastructure\Validation;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessOperatorShouldBeValidValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($operator, Constraint $constraint)
    {
        Assert::isInstanceOf($constraint, CompletenessOperatorShouldBeValid::class);
        if (!is_string($operator)) {
            return;
        }
        if ($this->isValid($operator)) {
            $this->context->buildViolation(
                CompletenessOperatorShouldBeValid::NOT_SUPPORTED_OPERATOR,
                [
                    '{{ operator }}' => $operator,
                ]
            )->addViolation();
        }
    }

    private function isValid(string $operator): bool
    {
        return in_array(
            $operator,
            [
                'ALL',
                Operators::GREATER_OR_EQUALS_THAN_ON_AT_LEAST_ONE_LOCALE,
                Operators::GREATER_OR_EQUALS_THAN_ON_ALL_LOCALES,
                Operators::LOWER_OR_EQUALS_THAN_ON_ALL_LOCALES,
            ],
            true
        );
    }
}
