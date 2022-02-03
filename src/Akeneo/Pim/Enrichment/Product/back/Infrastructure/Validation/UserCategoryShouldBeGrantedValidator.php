<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\Product\Infrastructure\Validation;

use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Product\Api\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\Domain\Model\Permission\AccessLevel;
use Akeneo\Pim\Enrichment\Product\Domain\Model\ProductIdentifier;
use Akeneo\Pim\Enrichment\Product\Domain\Query\IsUserCategoryGranted;
use Akeneo\Pim\Enrichment\Product\Infrastructure\AntiCorruptionLayer\Feature;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * Check the user has own permission on the product
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UserCategoryShouldBeGrantedValidator extends ConstraintValidator
{
    public function __construct(
        private IsUserCategoryGranted $isUserCategoryGranted,
        private UserRepositoryInterface $userRepository,
        private ProductRepositoryInterface $productRepository,
        private Feature $feature
    ) {
    }

    public function validate($command, Constraint $constraint): void
    {
        Assert::isInstanceOf($command, UpsertProductCommand::class);
        Assert::isInstanceOf($constraint, UserCategoryShouldBeGranted::class);

        if (!$this->feature->isEnabled(Feature::PERMISSION)) {
            return;
        }

        $product = $this->productRepository->findOneByIdentifier($command->productIdentifier());
        if (null === $product) {
            // TODO: if we create with a category, we have to check the category is granted
            return;
        }

        // TODO: validate the user exists (using sequence to not continue validation)
        $user = $this->userRepository->findOneBy(['id' => $command->userId()]);
        if (null === $user) {
            return;
        }

        if (!$this->isUserCategoryGranted->forProductAndAccessLevel(
            $command->userId(),
            ProductIdentifier::fromString($command->productIdentifier()),
            AccessLevel::OWN_PRODUCTS
        )) {
            $this->context->buildViolation('')->addViolation();
        }
    }
}
