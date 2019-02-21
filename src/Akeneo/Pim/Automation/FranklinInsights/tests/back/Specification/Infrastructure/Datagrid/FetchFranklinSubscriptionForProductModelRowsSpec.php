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

use Akeneo\Pim\Enrichment\Component\Product\Grid\Query\FetchProductAndProductModelRowsParameters;
use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\AdditionalProperty;
use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\Row;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use PhpSpec\ObjectBehavior;

class FetchFranklinSubscriptionForProductModelRowsSpec extends ObjectBehavior
{
    public function it_returns_an_empty_array(ProductQueryBuilderInterface $pqb): void
    {
        $queryParameters = new FetchProductAndProductModelRowsParameters($pqb->getWrappedObject(), [], '', '');
        $this->add($queryParameters, [])->shouldReturn([]);
    }

    public function it_adds_additional_data(ProductQueryBuilderInterface $pqb): void
    {
        $queryParameters = new FetchProductAndProductModelRowsParameters($pqb->getWrappedObject(), [], '', '');

        $row1 = Row::fromProductModel('product_123', '', new \DateTime(), new \DateTime(), '', null, 123, [], null, new ValueCollection());
        $newRow1 = $row1->addAdditionalProperty(new AdditionalProperty('franklin_subscription', null));

        $row2 = Row::fromProductModel('product_456', '', new \DateTime(), new \DateTime(), '', null, 456, [], null, new ValueCollection());
        $newRow2 = $row2->addAdditionalProperty(new AdditionalProperty('franklin_subscription', null));

        $this->add($queryParameters, [$row1, $row2])->shouldBeLike([$newRow1, $newRow2]);
    }
}
