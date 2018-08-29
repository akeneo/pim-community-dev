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
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use PhpSpec\ObjectBehavior;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class ProductSubscriptionSpec extends ObjectBehavior
{
    private $product;
    private $subscriptionId;
    private $suggestedData;

    public function let()
    {
        $this->product = new Product();
        $this->subscriptionId = 'foobar';
        $this->suggestedData = [];

        $this->beConstructedWith($this->product, $this->subscriptionId, $this->suggestedData);
    }

    public function it_is_a_product_subscription()
    {
        $this->shouldBeAnInstanceOf(ProductSubscription::class);
        $this->shouldImplement(ProductSubscriptionInterface::class);
    }

    public function it_gets_the_product_subscription_product()
    {
        $this->getProduct()->shouldReturn($this->product);
    }

    public function it_gets_the_product_subscription_id()
    {
        $this->getSubscriptionId()->shouldReturn($this->subscriptionId);
    }

    public function it_gets_the_product_subscription_suggested_data()
    {
        $this->getSuggestedData()->shouldReturn($this->suggestedData);
    }
}
