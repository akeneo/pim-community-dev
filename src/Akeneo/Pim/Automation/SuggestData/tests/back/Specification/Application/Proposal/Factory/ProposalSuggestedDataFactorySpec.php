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
use Akeneo\Pim\Automation\SuggestData\Application\Proposal\Factory\ProposalSuggestedDataFactory;
use Akeneo\Pim\Automation\SuggestData\Domain\Proposal\ValueObject\ProposalSuggestedData as WriteSuggestedData;
use Akeneo\Pim\Automation\SuggestData\Domain\Subscription\Model\ProductSubscription;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class ProposalSuggestedDataFactorySpec extends ObjectBehavior
{
    public function let(SuggestedDataNormalizer $normalizer, ProductRepositoryInterface $productRepository): void
    {
        $this->beConstructedWith($normalizer, $productRepository);
    }

    public function it_is_a_suggested_data_factory(): void
    {
        $this->shouldHaveType(ProposalSuggestedDataFactory::class);
    }

    // TODO APAI-244: remove this spec
    public function it_returns_null_if_product_is_not_categorized(
        $productRepository,
        ProductInterface $product
    ): void {
        $product->getCategoryCodes()->willReturn([]);
        $productRepository->find(42)->willReturn($product);

        $this->fromSubscription(new ProductSubscription(42, 'fake-id', []))->shouldReturn(
            null
        );
    }

    public function it_returns_null_if_suggested_data_cannot_be_normalized(
        $normalizer,
        $productRepository,
        ProductInterface $product,
        FamilyInterface $family
    ): void {
        $product->getCategoryCodes()->willReturn(['hitech']);
        $product->getFamily()->willReturn($family);
        $productRepository->find(42)->willReturn($product);

        $subscription = new ProductSubscription(42, 'fake-id', [['pimAttributeCode' => 'foo', 'value' => 'bar']]);
        $normalizer->normalize($subscription->getSuggestedData())->willThrow(new \InvalidArgumentException());

        $this->fromSubscription($subscription)->shouldReturn(null);
    }

    public function it_returns_null_if_suggested_values_are_empty(
        $normalizer,
        $productRepository,
        ProductInterface $product,
        FamilyInterface $family
    ): void {
        $product->getCategoryCodes()->willReturn(['hitech']);
        $family->getAttributeCodes()->willReturn(['att_1']);
        $product->getFamily()->willReturn($family);
        $productRepository->find(42)->willReturn($product);

        $subscription = new ProductSubscription(42, 'fake-id', []);
        $normalizer->normalize($subscription->getSuggestedData())->willReturn([]);

        $this->fromSubscription($subscription)->shouldReturn(null);
    }

    public function it_filters_attributes_which_are_not_in_the_family(
        $normalizer,
        $productRepository,
        ProductInterface $product,
        FamilyInterface $family
    ): void {
        $product->getCategoryCodes()->willReturn(['hitech']);
        $family->getAttributeCodes()->willReturn(['att_1']);
        $product->getFamily()->willReturn($family);
        $productRepository->find(42)->willReturn($product);

        $subscription = new ProductSubscription(42, 'fake-id', [
            [
                'pimAttributeCode' => 'att_1',
                'value' => 'bar',
            ],
            [
                'pimAttributeCode' => 'att_2',
                'value' => 'baz',
            ],
        ]);
        $normalizer->normalize($subscription->getSuggestedData())->willReturn(
            [
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
            ]
        );

        $return = $this->fromSubscription($subscription);
        $return->shouldBeAnInstanceOf(WriteSuggestedData::class);
        $return->getSuggestedValues()->shouldHaveKey('att_1');
        $return->getSuggestedValues()->shouldNotHaveKey('att_2');
    }
}
