<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Query\Doctrine;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductIdentifierValues;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Query\Product\SelectProductIdentifierValuesQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Query\Doctrine\SelectProductIdentifierValuesQuery;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SelectProductIdentifierValuesQuerySpec extends ObjectBehavior
{
    public function let(Connection $connection): void
    {
        $this->beConstructedWith($connection);
    }

    public function it_is_a_select_product_identifier_values_query(): void
    {
        $this->shouldImplement(SelectProductIdentifierValuesQueryInterface::class);
    }

    public function it_is_a_doctrine_implementation_of_a_select_product_identifier_values_query(): void
    {
        $this->shouldBeAnInstanceOf(SelectProductIdentifierValuesQuery::class);
    }

    public function it_returns_null_if_the_results_are_empty(
        $connection,
        Statement $statement
    ): void {
        $statement->fetchAll()->willReturn([]);
        $connection->executeQuery(Argument::type('string'), ['product_id' => 42])->willReturn($statement);

        $this->execute(42)->shouldReturn(null);
    }

    public function it_returns_a_product_identifier_values_read_model(
        $connection,
        Statement $statement
    ): void {
        $statement->fetchAll()->willReturn(
            [
                [
                    'identifier' => 'asin',
                    'value' => 'ABC456789',
                ],
                [
                    'identifier' => 'upc',
                    'value' => '012345678912',
                ],
            ]
        );
        $connection->executeQuery(Argument::type('string'), ['product_id' => 42])->willReturn($statement);

        $result = $this->execute(42);
        $result->shouldHaveType(ProductIdentifierValues::class);
        $result->identifierValues()->shouldBeLike(
            [
                'asin' => 'ABC456789',
                'upc' => '012345678912',
                'mpn' => null,
                'brand' => null,
            ]
        );
    }
}
