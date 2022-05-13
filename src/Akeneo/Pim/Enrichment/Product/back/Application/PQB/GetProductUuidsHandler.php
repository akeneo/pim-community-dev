<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Application\PQB;

use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\ApplyProductSearchQueryParametersToPQB;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\Exception\ViolationsException;
use Akeneo\Pim\Enrichment\Product\API\Query\GetProductUuids;
use Akeneo\Pim\Enrichment\Product\Domain\PQB\ProductQueryBuilderInterface;
use Akeneo\Pim\Enrichment\Product\Domain\PQB\ProductUuidQueryFetcher;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductUuidsHandler
{
    public function __construct(
        private ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        private ApplyProductSearchQueryParametersToPQB $applyProductSearchQueryParametersToPQB,
        private ProductUuidQueryFetcher $productUuidQueryFetcher,
        private ValidatorInterface $validator
    ) {
    }

    public function __invoke(GetProductUuids $getProductUuids): ProductUuidCursor
    {
        $violations = $this->validator->validate($getProductUuids);
        if (0 < $violations->count()) {
            throw new ViolationsException($violations);
        }

        $pqb = $this->productQueryBuilderFactory->create();
        $this->applyProductSearchQueryParametersToPQB->apply($pqb, $getProductUuids->searchFilters(), null, null, null);

        Assert::implementsInterface($pqb, ProductQueryBuilderInterface::class);
        $this->productUuidQueryFetcher->initialize($pqb->buildQuery());

        return ProductUuidCursor::createFromFetcher($this->productUuidQueryFetcher);
    }
}
