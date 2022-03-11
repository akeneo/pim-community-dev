<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Infrastructure\Validation;

use Akeneo\Pim\Enrichment\Category\API\Query\GetOwnedCategories;
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
final class CategoriesShouldBeEditableByUserValidator extends ConstraintValidator
{
    public function __construct(
        private GetOwnedCategories $getOwnedCategories,
        private GetViewableCategories $getViewableCategories
    ) {
    }

    public function validate($categoryUserIntent, Constraint $constraint): void
    {
        if (null === $categoryUserIntent) {
            return;
        }
        Assert::isInstanceOf($categoryUserIntent, CategoryUserIntent::class);
        Assert::isInstanceOf($constraint, CategoriesShouldBeEditableByUser::class);

        $command = $this->context->getRoot();
        Assert::isInstanceOf($command, UpsertProductCommand::class);

        $categoryCodes = $categoryUserIntent->categoryCodes();
        if ([] === $categoryCodes) {
            return;
        }

        if (!$this->checkUserIsStillOwner($command->userId(), $categoryCodes)) {
            $this->context->buildViolation('pim_enrich.product.validation.upsert.no_own_access_on_category')->addViolation();
            return;
        }

        if (!$this->checkCategoriesAreViewable($command->userId(), $categoryCodes)) {
            $this->context->buildViolation('pim_enrich.product.validation.upsert.no_view_access_on_category')->addViolation();
        }
    }

    /**
     * @param array<string> $categoryCodes
     */
    private function checkUserIsStillOwner(int $userId, array $categoryCodes): bool
    {
        $ownedCategories = $this->getOwnedCategories->forUserId($categoryCodes, $userId);

        return \count($ownedCategories) > 0;
    }

    /**
     * @param array<string> $categoryCodes
     */
    private function checkCategoriesAreViewable(int $userId, array $categoryCodes): bool
    {
        $viewableCategories = $this->getViewableCategories->forUserId($categoryCodes, $userId);

        return \count($viewableCategories) === \count($categoryCodes);
    }
}
