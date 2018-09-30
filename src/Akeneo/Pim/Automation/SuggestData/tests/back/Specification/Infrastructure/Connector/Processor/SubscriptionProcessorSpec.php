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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\Connector\Processor;

use Akeneo\Pim\Automation\SuggestData\Domain\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscription;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionRequest;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\IdentifiersMappingRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Connector\Processor\SubscriptionProcessor;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class SubscriptionProcessorSpec extends ObjectBehavior
{
    public function let(
        ProductSubscriptionRepositoryInterface $productSubscriptionRepository,
        IdentifiersMappingRepositoryInterface $identifiersMappingRepository,
        AttributeInterface $asin
    ): void {
        $identifiersMappingRepository->find()->willReturn(
            new IdentifiersMapping(['asin' => $asin->getWrappedObject()])
        );
        $this->beConstructedWith($productSubscriptionRepository, $identifiersMappingRepository);
    }

    public function it_is_an_item_processor(): void
    {
        $this->shouldImplement(ItemProcessorInterface::class);
    }

    public function it_is_a_subscription_processor(): void
    {
        $this->shouldHaveType(SubscriptionProcessor::class);
    }

    public function it_does_not_process_a_variant_product(
        ProductInterface $product
    ): void {
        $product->isVariant()->willReturn(true);
        $product->getIdentifier()->willReturn('foo');

        $this->shouldThrow(InvalidItemException::class)->during('process', [$product]);
    }

    public function it_does_not_process_a_product_without_family(
        ProductInterface $product
    ): void {
        $product->isVariant()->willReturn(false);
        $product->getFamily()->willReturn(null);
        $product->getIdentifier()->willReturn('foo');

        $this->shouldThrow(InvalidItemException::class)->during('process', [$product]);
    }

    public function it_does_not_process_a_product_already_subscribed(
        $productSubscriptionRepository,
        ProductInterface $product,
        FamilyInterface $family
    ): void {
        $product->isVariant()->willReturn(false);
        $product->getFamily()->willReturn($family);
        $product->getId()->willReturn(42);
        $productSubscriptionRepository->findOneByProductId(42)->willReturn(
            new ProductSubscription($product->getWrappedObject(), 'fake-subscription-id')
        );
        $product->getIdentifier()->willReturn('foo');

        $this->shouldThrow(InvalidItemException::class)->during('process', [$product]);
    }

    public function it_does_not_process_a_product_without_identifier_values(
        $productSubscriptionRepository,
        $asin,
        ProductInterface $product,
        FamilyInterface $family
    ): void {
        $product->isVariant()->willReturn(false);
        $product->getFamily()->willReturn($family);
        $product->getId()->willReturn(42);
        $productSubscriptionRepository->findOneByProductId(42)->willReturn(null);

        $asin->getCode()->willReturn('asin');
        $product->getValue('asin')->willReturn(null);

        $product->getIdentifier()->willReturn('foo');

        $this->shouldThrow(InvalidItemException::class)->during('process', [$product]);
    }

    public function it_successfully_processes_a_product(
        $productSubscriptionRepository,
        $asin,
        ProductInterface $product,
        FamilyInterface $family,
        ValueInterface $asinValue
    ): void {
        $product->isVariant()->willReturn(false);
        $product->getFamily()->willReturn($family);
        $product->getId()->willReturn(42);
        $productSubscriptionRepository->findOneByProductId(42)->willReturn(null);

        $asin->getCode()->willReturn('asin');
        $product->getValue('asin')->willReturn($asinValue);

        $asinValue->hasData()->willReturn(true);
        $asinValue->__toString()->willReturn('ABC123');

        $request = $this->process($product);
        $request->shouldHaveType(ProductSubscriptionRequest::class);
    }
}
