<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Application\PQB;

use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\ApplyProductSearchQueryParametersToPQB;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface as LegacyProductQueryBuilderInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\Exception\ViolationsException;
use Akeneo\Pim\Enrichment\Product\API\Query\GetProductUuidsQuery;
use Akeneo\Pim\Enrichment\Product\API\Query\ProductUuidCursorInterface;
use Akeneo\Pim\Enrichment\Product\Domain\PQB\ProductQueryBuilderInterface;
use Akeneo\Pim\Enrichment\Product\Domain\PQB\ProductUuidCursor;
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
        private ProductQueryBuilderInterface $pqb,
        private ApplyProductSearchQueryParametersToPQB $applyProductSearchQueryParametersToPQB,
        private ProductUuidQueryFetcher $productUuidQueryFetcher,
        private ValidatorInterface $validator
    ) {
    }

    public function __invoke(GetProductUuidsQuery $getProductUuidsQuery): ProductUuidCursorInterface
    {
        $violations = $this->validator->validate($getProductUuidsQuery);
        if (0 < $violations->count()) {
            throw new ViolationsException($violations);
        }

        Assert::implementsInterface($this->pqb, LegacyProductQueryBuilderInterface::class);
        $this->applyProductSearchQueryParametersToPQB->apply(
            $this->pqb,
            $getProductUuidsQuery->searchFilters(),
            null,
            null,
            null
        );
        $this->productUuidQueryFetcher->initialize($this->pqb->buildQuery(
            $getProductUuidsQuery->userId(),
            $getProductUuidsQuery->searchAfterUuid()
        ));

        return ProductUuidCursor::createFromFetcher($this->productUuidQueryFetcher);
    }
}
