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

use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionResponse;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class ProductSubscriptionResponseSpec extends ObjectBehavior
{
    public function it_is_a_product_subscription_response(ProductInterface $product)
    {
        $this->beConstructedWith($product, 'subscription-id', []);
        $this->shouldHaveType(ProductSubscriptionResponse::class);
    }

    public function it_cannot_be_instantiated_with_an_empty_subscription_id(ProductInterface $product)
    {
        $this->beConstructedWith($product, '', []);
        $this->shouldThrow(new \InvalidArgumentException('subscription id cannot be empty'))->duringInstantiation();
    }

    public function it_exposes_the_product(ProductInterface $product)
    {
        $this->beConstructedWith($product, 'some-subscription-id', []);
        $this->getProduct()->shouldReturn($product);
    }

    public function it_exposes_the_subscription_id(ProductInterface $product)
    {
        $this->beConstructedWith($product, 'a-random-id', []);
        $this->getSubscriptionId()->shouldReturn('a-random-id');
    }

    public function it_exposes_the_suggested_data(ProductInterface $product)
    {
        $suggestedData = [
            'foo' => 'bar',
            'bar' => 'baz',
        ];
        $this->beConstructedWith($product, 'subscription-id', $suggestedData);
        $this->getSuggestedData()->shouldReturn($suggestedData);
    }
}
