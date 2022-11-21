<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Application\PQB;

use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\ApplyProductSearchQueryParametersToPQB;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface as LegacyProductQueryBuilderInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\Exception\ViolationsException;
use Akeneo\Pim\Enrichment\Product\API\Query\GetProductUuidsQuery;
use Akeneo\Pim\Enrichment\Product\Application\PQB\GetProductUuidsHandler;
use Akeneo\Pim\Enrichment\Product\Domain\PQB\ProductQueryBuilderInterface;
use Akeneo\Pim\Enrichment\Product\Domain\PQB\ProductUuidCursor;
use Akeneo\Pim\Enrichment\Product\Domain\PQB\ProductUuidQueryFetcher;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GetProductUuidsHandlerSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $channelRepository,
        ProductUuidQueryFetcher $productUuidQueryFetcher,
        ValidatorInterface $validator
    ) {
        $pqb = new class implements ProductQueryBuilderInterface, LegacyProductQueryBuilderInterface {
            public function buildQuery(?int $userId, ?UuidInterface $searchAfterUuid = null): array
            {
                return ['the query'];
            }

            public function addFilter($field, $operator, $value, array $context = [])
            {
            }

            public function addSorter($field, $direction, array $context = [])
            {
            }

            public function getRawFilters()
            {
            }

            public function getQueryBuilder()
            {
            }

            public function setQueryBuilder($queryBuilder)
            {
            }

            public function execute()
            {
            }
        };
        $applyProductSearchQueryParametersToPQB = new ApplyProductSearchQueryParametersToPQB(
            $channelRepository->getWrappedObject()
        );
        $this->beConstructedWith($pqb, $applyProductSearchQueryParametersToPQB, $productUuidQueryFetcher, $validator);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(GetProductUuidsHandler::class);
    }

    function it_returns_a_cursor(
        ValidatorInterface $validator,
        ConstraintViolationList $constraintViolationList,
        ProductUuidQueryFetcher $productUuidQueryFetcher
    ) {
        $query = new GetProductUuidsQuery([], 1);
        $constraintViolationList->count()->willReturn(0);
        $validator->validate($query)->willReturn($constraintViolationList);

        $productUuidQueryFetcher->initialize(['the query'])->shouldBeCalledOnce();

        $this->__invoke($query)->shouldHaveType(ProductUuidCursor::class);
    }

    function it_throws_an_exception_when_query_is_not_valid(
        ValidatorInterface $validator,
        ConstraintViolationList $constraintViolationList
    ) {
        $query = new GetProductUuidsQuery([], 1);
        $constraintViolationList->count()->willReturn(5);
        $constraintViolationList->__toString()->willReturn('message');
        $validator->validate($query)->willReturn($constraintViolationList);

        $this->shouldThrow(ViolationsException::class)->during('__invoke', [$query]);
    }
}
