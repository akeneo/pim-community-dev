<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Datagrid;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Query\Product\ProductSubscriptionsExistQueryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Grid\Query\FetchProductAndProductModelRowsParameters;
use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\AdditionalProperty;
use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\Row;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use PhpSpec\ObjectBehavior;

class FetchFranklinSubscriptionForProductRowsSpec extends ObjectBehavior
{
    public function let(ProductSubscriptionsExistQueryInterface $productSubscriptionsExistQuery): void
    {
        $this->beConstructedWith($productSubscriptionsExistQuery);
    }

    public function it_returns_an_empty_array(ProductQueryBuilderInterface $pqb): void
    {
        $queryParameters = new FetchProductAndProductModelRowsParameters($pqb->getWrappedObject(), [], '', '');
        $this->add($queryParameters, [])->shouldReturn([]);
    }

    public function it_adds_additional_data(ProductQueryBuilderInterface $pqb, $productSubscriptionsExistQuery): void
    {
        $queryParameters = new FetchProductAndProductModelRowsParameters($pqb->getWrappedObject(), [], '', '');
        $this->add($queryParameters, [])->shouldReturn([]);

        $row1 = Row::fromProduct('product_123', null, [], true, new \DateTime(), new \DateTime(), '', null, null, 123, null, new ValueCollection());
        $newRow1 = $row1->addAdditionalProperty(new AdditionalProperty('franklin_subscription', 'Enabled'));

        $row2 = Row::fromProduct('product_456', null, [], true, new \DateTime(), new \DateTime(), '', null, null, 456, null, new ValueCollection());
        $newRow2 = $row2->addAdditionalProperty(new AdditionalProperty('franklin_subscription', 'Disabled'));

        $productSubscriptionsExistQuery->execute([123, 456])->willReturn([123 => true, 456 => false]);
        $this->add($queryParameters, [$row1, $row2])->shouldBeLike([$newRow1, $newRow2]);
    }
}
