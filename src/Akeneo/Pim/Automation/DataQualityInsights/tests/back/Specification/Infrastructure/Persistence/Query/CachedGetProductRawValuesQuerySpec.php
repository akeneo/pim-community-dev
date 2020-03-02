<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\ResultStatement;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

final class CachedGetProductRawValuesQuerySpec extends ObjectBehavior
{
    public function let(Connection $dbConnection)
    {
        $this->beConstructedWith($dbConnection);
    }

    public function it_keeps_in_cache_the_last_fetched_raw_values(Connection $dbConnection, ResultStatement $statement)
    {
        $dbConnection->executeQuery(Argument::cetera())
            ->shouldBeCalledOnce()
            ->willReturn($statement);

        $statement->fetchColumn(Argument::any())
            ->shouldBeCalledOnce()
            ->willReturn('{"name":{"mobile":{"en_US":"Ziggy"}}}');

        $productId = new ProductId(42);
        $expectedRawValues = [
            'name' => [
                'mobile' => [
                    'en_US' => 'Ziggy',
                ],
            ],
        ];

        $this->execute($productId)->shouldBeLike($expectedRawValues);

        // The second call should use the cache
        $this->execute($productId)->shouldBeLike($expectedRawValues);
    }
}
