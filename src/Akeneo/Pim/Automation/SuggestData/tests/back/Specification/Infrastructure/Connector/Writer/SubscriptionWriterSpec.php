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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\Connector\Writer;

use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\SubscriptionProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscription;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionRequest;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionResponse;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionResponseCollection;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\IdentifiersMappingRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Connector\Writer\SubscriptionWriter;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class SubscriptionWriterSpec extends ObjectBehavior
{
    public function let(
        SubscriptionProviderInterface $subscriptionProvider,
        ProductSubscriptionRepositoryInterface $productSubscriptionRepository,
        IdentifiersMappingRepositoryInterface $identifiersMappingRepository,
        StepExecution $stepExecution,
        IdentifiersMapping $identifiersMapping
    ): void {
        $identifiersMappingRepository->find()->willReturn($identifiersMapping);
        $this->beConstructedWith($subscriptionProvider, $productSubscriptionRepository, $identifiersMappingRepository);
        $this->setStepExecution($stepExecution);
        $this->initialize();
    }

    public function it_is_an_item_writer(): void
    {
        $this->shouldImplement(ItemWriterInterface::class);
    }

    public function it_is_step_execution_aware(): void
    {
        $this->shouldImplement(StepExecutionAwareInterface::class);
    }

    public function it_is_a_subscription_writer(): void
    {
        $this->shouldHaveType(SubscriptionWriter::class);
    }

    public function it_subscribes_items(
        $subscriptionProvider,
        $productSubscriptionRepository,
        $identifiersMapping,
        $stepExecution,
        ProductInterface $product1,
        ProductInterface $product2,
        AttributeInterface $asin
    ): void {
        $asin->getCode()->willReturn('asin');
        $identifiersMapping->getIterator()->willReturn(new \ArrayIterator(['asin' => $asin]));

        $product1->getId()->willReturn(42);
        $product2->getId()->willReturn(50);

        $items = [
            new ProductSubscriptionRequest($product1->getWrappedObject()),
            new ProductSubscriptionRequest($product2->getWrappedObject()),
        ];

        $collection = new ProductSubscriptionResponseCollection([]);
        $collection->add(new ProductSubscriptionResponse(42, '123-465-789', [], false));
        $collection->add(new ProductSubscriptionResponse(50, 'abc-def-987', [], false));

        $subscriptionProvider->bulkSubscribe($items)->willReturn($collection);

        $stepExecution->incrementSummaryInfo('subscribed')->shouldBeCalledTimes(2);
        $productSubscriptionRepository->save(Argument::type(ProductSubscription::class))->shouldBeCalledTimes(2);

        $this->write($items)->shouldReturn(null);
    }

    public function it_handles_warnings_returned_during_subscription(
        $subscriptionProvider,
        $productSubscriptionRepository,
        $identifiersMapping,
        $stepExecution,
        ProductInterface $product1,
        ProductInterface $product2,
        AttributeInterface $upc
    ): void {
        $upc->getCode()->willReturn('pim_upc');

        $identifiersMapping->getIterator()->willReturn(new \ArrayIterator(['pim_upc' => $upc]));

        $product1->getId()->willReturn(42);
        $product1->getIdentifier()->willReturn('sku_for_my_invalid_upc');

        $product2->getId()->willReturn(50);

        $items = [
            new ProductSubscriptionRequest($product1->getWrappedObject()),
            new ProductSubscriptionRequest($product2->getWrappedObject()),
        ];

        $collection = new ProductSubscriptionResponseCollection([
            42 => 'Invalid UPC: \'123456\'',
        ]);
        $collection->add(new ProductSubscriptionResponse(50, 'abc-def-987', [], false));

        $subscriptionProvider->bulkSubscribe($items)->willReturn($collection);
        $stepExecution->addWarning(
            'An error was returned by Franklin during subscription: %error%',
            ['%error%' => 'Invalid UPC: \'123456\''],
            new DataInvalidItem([
                'identifier' => 'sku_for_my_invalid_upc',
            ])
        )->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('subscribed')->shouldBeCalledTimes(1);
        $productSubscriptionRepository->save(Argument::type(ProductSubscription::class))->shouldBeCalledTimes(1);

        $this->write($items)->shouldReturn(null);
    }
}
