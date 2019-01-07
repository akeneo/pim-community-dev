<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\ProductSubscription;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\ValueObject\SuggestedData;
use PhpSpec\ObjectBehavior;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class ProductSubscriptionSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(42, 'foobar', ['asin' => '72527273070']);
    }

    public function it_is_a_product_subscription(): void
    {
        $this->shouldBeAnInstanceOf(ProductSubscription::class);
        $this->shouldImplement(ProductSubscription::class);
    }

    public function it_gets_the_product_id(): void
    {
        $this->getProductId()->shouldReturn(42);
    }

    public function it_gets_the_subscription_id(): void
    {
        $this->getSubscriptionId()->shouldReturn('foobar');
    }

    public function it_sets_the_suggested_data(): void
    {
        $suggestedData = new SuggestedData([]);
        $this->setSuggestedData($suggestedData)->shouldReturn($this);
    }

    public function it_gets_the_suggested_data(): void
    {
        $suggestedData = new SuggestedData(
            [
                [
                    'pimAttributeCode' => 'upc',
                    'value' => '42',
                ],
            ]
        );
        $this->setSuggestedData($suggestedData);

        $this->getSuggestedData()->shouldReturn($suggestedData);
    }

    public function it_sets_missing_mapping(): void
    {
        $this->markAsMissingMapping(false)->shouldReturn($this);
    }

    public function it_gets_missing_mapping(): void
    {
        $this->isMappingMissing()->shouldReturn(false);
    }

    public function it_exposes_requested_identifier_values(): void
    {
        $requestedIdentifierValues = $this->requestedIdentifierValues();

        $requestedIdentifierValues->shouldHaveKeyWithValue('asin', '72527273070');
        $requestedIdentifierValues->shouldNotHaveKey('upc');
        $requestedIdentifierValues->shouldNotHaveKey('mpn');
        $requestedIdentifierValues->shouldNotHaveKey('brand');
    }
}
