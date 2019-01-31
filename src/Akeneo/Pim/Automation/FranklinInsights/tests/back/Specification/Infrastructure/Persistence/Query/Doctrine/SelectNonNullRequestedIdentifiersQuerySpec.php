<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Query\Doctrine;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Query\SelectNonNullRequestedIdentifiersQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Query\Doctrine\SelectNonNullRequestedIdentifiersQuery;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SelectNonNullRequestedIdentifiersQuerySpec extends ObjectBehavior
{
    public function let(Connection $connection): void
    {
        $this->beConstructedWith($connection);
    }

    public function it_is_a_select_non_null_requested_identifiers_query(): void
    {
        $this->shouldImplement(SelectNonNullRequestedIdentifiersQueryInterface::class);
    }

    public function it_is_a_doctrine_implementation_of_a_select_non_null_requested_identifiers_query(): void
    {
        $this->shouldHaveType(SelectNonNullRequestedIdentifiersQuery::class);
    }

    public function it_returns_an_empty_array_if_no_franklin_identifiers_are_provided(): void
    {
        $this->execute([], 0, 10)->shouldReturn([]);
    }

    public function it_returns_non_null_requested_identifiers($connection, Statement $statement): void
    {
        $statement->fetchAll()->willReturn([
            [
                'product_id' => 42,
                'requested_asin' => 'ABC123',
                'requested_upc' => '456123',
                'requested_brand' => 'Akeneo',
                'requested_mpn' => 'pim',
            ],
            [
                'product_id' => 44,
                'requested_asin' => 'DEF456',
                'requested_upc' => '123456',
                'requested_brand' => 'Akeneo',
                'requested_mpn' => 'ask-franklin',
            ],
        ]);
        $connection->executeQuery(Argument::type('string'), Argument::cetera())->willReturn($statement);

        $this->execute(['asin'], 0, 10)->shouldReturn([
            42 => [
                'asin' => 'ABC123',
                'upc' => '456123',
                'brand' => 'Akeneo',
                'mpn' => 'pim',
            ],
            44 => [
                'asin' => 'DEF456',
                'upc' => '123456',
                'brand' => 'Akeneo',
                'mpn' => 'ask-franklin',
            ],
        ]);
    }

    public function it_filters_null_requested_identifier_values($connection, Statement $statement): void
    {
        $statement->fetchAll()->willReturn(
            [
                [
                    'product_id' => 42,
                    'requested_asin' => 'ABC123',
                    'requested_upc' => '456123',
                    'requested_brand' => null,
                    'requested_mpn' => null,
                ],
                [
                    'product_id' => 44,
                    'requested_asin' => 'DEF456',
                    'requested_upc' => null,
                    'requested_brand' => 'Akeneo',
                    'requested_mpn' => 'ask-franklin',
                ],
            ]
        );
        $connection->executeQuery(Argument::type('string'), Argument::cetera())->willReturn($statement);

        $this->execute(['asin'], 0, 10)->shouldReturn(
            [
                42 => [
                    'asin' => 'ABC123',
                    'upc' => '456123',
                ],
                44 => [
                    'asin' => 'DEF456',
                    'brand' => 'Akeneo',
                    'mpn' => 'ask-franklin',
                ],
            ]
        );
    }
}
