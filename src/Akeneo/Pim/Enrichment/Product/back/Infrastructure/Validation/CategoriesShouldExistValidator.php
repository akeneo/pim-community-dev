<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Infrastructure\Validation;

use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\CategoryUserIntent;
use Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CategoriesShouldExistValidator extends ConstraintValidator
{
    public function __construct(private CategoryRepositoryInterface $categoryRepository)
    {
    }

    public function validate($categoryUserIntent, Constraint $constraint): void
    {
        if(null === $categoryUserIntent) {
            return;
        }
        Assert::isInstanceOf($categoryUserIntent, CategoryUserIntent::class);
        Assert::isInstanceOf($constraint, CategoriesShouldExist::class);

        $command = $this->context->getRoot();
        Assert::isInstanceOf($command, UpsertProductCommand::class);

        $categoryCodes = $categoryUserIntent->categoryCodes();

        foreach ($categoryCodes as $categoryCode) {
            $category = $this->categoryRepository->findOneByIdentifier($categoryCode);

            if (null === $category) {
                $this->context->buildViolation($constraint->message, ['{{ categoryCode }}' => $categoryCode])->addViolation();
                return;
            }
        }
    }
}
