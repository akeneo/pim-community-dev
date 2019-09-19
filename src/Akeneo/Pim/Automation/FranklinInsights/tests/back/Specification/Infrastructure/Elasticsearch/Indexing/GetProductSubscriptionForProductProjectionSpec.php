<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Elasticsearch\Indexing;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Query\Product\ProductSubscriptionsExistQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Elasticsearch\Indexing\GetProductSubscriptionForProductProjection;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\GetAdditionalPropertiesForProductProjectionInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetProductSubscriptionForProductProjectionSpec extends ObjectBehavior
{
    function let(ProductSubscriptionsExistQueryInterface $productSubscriptionsExistQuery)
    {
        $this->beConstructedWith($productSubscriptionsExistQuery);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(GetProductSubscriptionForProductProjection::class);
    }

    function it_implements_the_get_product_data_for_indexation_interface()
    {
        $this->shouldImplement(GetAdditionalPropertiesForProductProjectionInterface::class);
    }

    function it_returns_an_array_with_valid_data(ProductSubscriptionsExistQueryInterface $productSubscriptionsExistQuery)
    {
        $productSubscriptionsExistQuery->executeWithIdentifiers(['foo', 'bar'])
            ->willReturn(['foo' => true, 'bar' => false]);

        $this->fromProductIdentifiers(['foo', 'bar'])->shouldReturn([
            'foo' => ['franklin_subscription' => true],
            'bar' => ['franklin_subscription' => false],
        ]);
    }

    function it_returns_an_empty_array_if_no_identifiers_are_provided(ProductSubscriptionsExistQueryInterface $productSubscriptionsExistQuery)
    {
        $productSubscriptionsExistQuery->executeWithIdentifiers(Argument::cetera())->shouldNotBeCalled();

        $this->fromProductIdentifiers([])->shouldReturn([]);
    }
}
