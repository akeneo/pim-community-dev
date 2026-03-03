<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Infrastructure\Validation;

use Akeneo\Pim\Enrichment\Category\API\Query\GetViewableCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\CategoryUserIntent;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CategoriesShouldBeViewableValidator extends ConstraintValidator
{
    public function __construct(private GetViewableCategories $getViewableCategories)
    {
    }

    public function validate($categoryUserIntent, Constraint $constraint): void
    {
        if (null === $categoryUserIntent) {
            return;
        }
        Assert::isInstanceOf($categoryUserIntent, CategoryUserIntent::class);
        Assert::isInstanceOf($constraint, CategoriesShouldBeViewable::class);

        $categoryCodes = \array_unique($categoryUserIntent->categoryCodes());
        if ([] === $categoryCodes) {
            return;
        }
        $command = $this->context->getRoot();
        Assert::isInstanceOf($command, UpsertProductCommand::class);

        if (-1 === (int) $command->userId()) {
            return;
        }

        $notViewableCategoryCodes = \array_diff(
            $categoryCodes,
            $this->getViewableCategories->forUserId($categoryCodes, $command->userId())
        );

        if ([] !== $notViewableCategoryCodes) {
            $this->context->buildViolation(
                $constraint->message,
                [
                    '{{ categoryCodes }}' => \implode(', ', $notViewableCategoryCodes),
                    '%count%' => \count($notViewableCategoryCodes),
                ]
            )->addViolation();
        }
    }
}
