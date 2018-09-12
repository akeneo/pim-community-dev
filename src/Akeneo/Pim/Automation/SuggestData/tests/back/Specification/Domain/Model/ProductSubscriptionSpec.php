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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Domain\Model;

use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscription;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\SuggestedData;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use PhpSpec\ObjectBehavior;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class ProductSubscriptionSpec extends ObjectBehavior
{
    private $product;
    private $subscriptionId;

    public function let()
    {
        $this->product = new Product();
        $this->subscriptionId = 'foobar';

        $this->beConstructedWith($this->product, $this->subscriptionId);
    }

    public function it_is_a_product_subscription()
    {
        $this->shouldBeAnInstanceOf(ProductSubscription::class);
        $this->shouldImplement(ProductSubscription::class);
    }

    public function it_gets_the_product()
    {
        $this->getProduct()->shouldReturn($this->product);
    }

    public function it_gets_the_subscription_id()
    {
        $this->getSubscriptionId()->shouldReturn($this->subscriptionId);
    }

    public function it_sets_the_suggested_data()
    {
        $suggestedData = new SuggestedData([]);
        $this->setSuggestedData($suggestedData)->shouldReturn($this);
    }

    public function it_gets_the_suggested_data()
    {
        $suggestedData = new SuggestedData(['upc' => '42']);
        $this->setSuggestedData($suggestedData);

        $this->getSuggestedData()->shouldReturn($suggestedData);
    }
}
