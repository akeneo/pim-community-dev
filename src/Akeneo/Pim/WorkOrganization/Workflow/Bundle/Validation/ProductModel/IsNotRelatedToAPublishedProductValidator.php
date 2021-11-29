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

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Validation\ProductModel;

use Akeneo\Pim\Enrichment\Component\Product\Command\ProductModel\RemoveProductModelCommand;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\PublishedProductRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class IsNotRelatedToAPublishedProductValidator extends ConstraintValidator
{
    private ProductModelRepositoryInterface $productModelRepository;
    private PublishedProductRepositoryInterface $publishedRepository;

    public function __construct(
        ProductModelRepositoryInterface $productModelRepository,
        PublishedProductRepositoryInterface $publishedRepository
    ) {
        $this->productModelRepository = $productModelRepository;
        $this->publishedRepository = $publishedRepository;
    }

    public function validate($command, Constraint $constraint): void
    {
        Assert::isInstanceOf($command, RemoveProductModelCommand::class);
        Assert::isInstanceOf($constraint, IsNotRelatedToAPublishedProduct::class);

        $productModel = $this->productModelRepository->findOneByIdentifier($command->productModelCode());
        if (null === $productModel) {
            return;
        }

        if (0 < $this->publishedRepository->countPublishedVariantProductsForProductModel($productModel)) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
