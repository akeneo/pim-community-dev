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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Application\Proposal\Factory;

use Akeneo\Pim\Automation\SuggestData\Application\Normalizer\Standard\SuggestedDataNormalizer;
use Akeneo\Pim\Automation\SuggestData\Application\Proposal\Factory\SuggestedDataFactory;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscription;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\SuggestedData;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Write\SuggestedData as WriteSuggestedData;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class SuggestedDataFactorySpec extends ObjectBehavior
{
    public function let(SuggestedDataNormalizer $normalizer): void
    {
        $this->beConstructedWith($normalizer);
    }

    public function it_is_a_suggested_data_factory(): void
    {
        $this->shouldHaveType(SuggestedDataFactory::class);
    }

    public function it_returns_null_if_product_is_not_categorized(
        ProductInterface $product
    ): void {
        $product->getCategoryCodes()->willReturn([]);
        $this->fromSubscription(new ProductSubscription($product->getWrappedObject(), 'fake-id', []))->shouldReturn(null);
    }

    public function it_returns_null_if_suggested_data_cannot_be_normalized(
        $normalizer,
        ProductInterface $product,
        FamilyInterface $family,
        ProductSubscription $subscription
    ): void {
        $product->getCategoryCodes()->willReturn(['hitech']);
        $product->getFamily()->willReturn($family);

        $subscription->getProduct()->willReturn($product);
        $suggestedData = new SuggestedData(['foo' => 'bar']);
        $subscription->getSuggestedData()->willReturn($suggestedData);
        $normalizer->normalize($suggestedData)->willThrow(new \InvalidArgumentException());

        $this->fromSubscription($subscription)->shouldReturn(null);
    }

    public function it_returns_null_if_suggested_values_are_empty(
        $normalizer,
        ProductInterface $product,
        FamilyInterface $family,
        ProductSubscription $subscription
    ): void {
        $product->getCategoryCodes()->willReturn(['hitech']);
        $family->getAttributeCodes()->willReturn(['att_1']);
        $product->getFamily()->willReturn($family);

        $subscription->getProduct()->willReturn($product);
        $suggestedData = new SuggestedData(['foo' => 'bar']);
        $subscription->getSuggestedData()->willReturn($suggestedData);
        $normalizer->normalize($suggestedData)->willReturn([]);

        $this->fromSubscription($subscription)->shouldReturn(null);
    }

    public function it_filters_attributes_which_are_not_in_the_family(
        $normalizer,
        ProductInterface $product,
        FamilyInterface $family,
        ProductSubscription $subscription
    ): void {
        $product->getCategoryCodes()->willReturn(['hitech']);
        $family->getAttributeCodes()->willReturn(['att_1']);
        $product->getFamily()->willReturn($family);

        $subscription->getProduct()->willReturn($product);
        $subscription->getSubscriptionId()->willReturn('abc-123');

        $suggestedData = new SuggestedData(['foo' => 'bar']);
        $subscription->getSuggestedData()->willReturn($suggestedData);
        $normalizer->normalize($suggestedData)->willReturn([
            'att_1' => [
                'scope' => null,
                'locale' => null,
                'data' => 'bar',
            ],
            'att_2' => [
                'scope' => null,
                'locale' => null,
                'data' => 'baz',
            ],
        ]);

        $return = $this->fromSubscription($subscription);
        $return->shouldBeAnInstanceOf(WriteSuggestedData::class);
        $return->getSuggestedValues()->shouldHaveKey('att_1');
        $return->getSuggestedValues()->shouldNotHaveKey('att_2');
    }
}
