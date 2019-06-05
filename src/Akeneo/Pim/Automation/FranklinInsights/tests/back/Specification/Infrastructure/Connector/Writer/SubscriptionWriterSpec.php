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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\Writer;

use Akeneo\Pim\Automation\FranklinInsights\Application\DataProvider\SubscriptionProviderInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Model\Read\Family;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\ProductId;
use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Repository\IdentifiersMappingRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Events\ProductSubscribed;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\ProductSubscription;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductIdentifierValues;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductSubscriptionResponse;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductSubscriptionResponseCollection;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Write\ProductSubscriptionRequest;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\ValueObject\SubscriptionId;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\Writer\SubscriptionWriter;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class SubscriptionWriterSpec extends ObjectBehavior
{
    public function let(
        SubscriptionProviderInterface $subscriptionProvider,
        ProductSubscriptionRepositoryInterface $productSubscriptionRepository,
        StepExecution $stepExecution,
        EventDispatcherInterface $eventDispatcher
    ): void {
        $this->beConstructedWith(
            $subscriptionProvider,
            $productSubscriptionRepository,
            $eventDispatcher
        );
        $this->setStepExecution($stepExecution);
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
        $stepExecution,
        $eventDispatcher
    ): void {
        $family = new Family(new FamilyCode('a_family'), []);
        $productId1 = new ProductId(42);
        $productId2 = new ProductId(50);

        $items = [
            new ProductSubscriptionRequest(
                $productId1,
                $family,
                new ProductIdentifierValues($productId1, ['asin' => '123456']),
                'product_1'
            ),
            new ProductSubscriptionRequest(
                $productId2,
                $family,
                new ProductIdentifierValues($productId2, ['asin' => '654321']),
                'product_2'
            ),
        ];

        $collection = new ProductSubscriptionResponseCollection([]);
        $collection->add(new ProductSubscriptionResponse($productId1, new SubscriptionId('123-465-789'), [], false, false));
        $collection->add(new ProductSubscriptionResponse($productId2, new SubscriptionId('abc-def-987'), [], false, false));

        $subscriptionProvider->bulkSubscribe($items)->willReturn($collection);

        $stepExecution->incrementSummaryInfo('subscribed')->shouldBeCalledTimes(2);
        $productSubscriptionRepository->save(Argument::type(ProductSubscription::class))->shouldBeCalledTimes(2);

        $eventDispatcher->dispatch(ProductSubscribed::EVENT_NAME, Argument::that(function ($productSubscribed) {
            return $productSubscribed instanceof ProductSubscribed &&
                '123-465-789' === (string) $productSubscribed->getProductSubscription()->getSubscriptionId();
        }))->shouldBeCalled();

        $eventDispatcher->dispatch(ProductSubscribed::EVENT_NAME, Argument::that(function ($productSubscribed) {
            return $productSubscribed instanceof ProductSubscribed &&
                'abc-def-987' === (string) $productSubscribed->getProductSubscription()->getSubscriptionId();
        }))->shouldBeCalled();

        $this->write($items)->shouldReturn(null);
    }

    public function it_handles_warnings_returned_during_subscription(
        $subscriptionProvider,
        $productSubscriptionRepository,
        $stepExecution,
        $eventDispatcher
    ): void {
        $family = new Family(new FamilyCode('a_family'), []);
        $productId1 = new ProductId(42);
        $productId2 = new ProductId(50);

        $items = [
            new ProductSubscriptionRequest(
                $productId1,
                $family,
                new ProductIdentifierValues($productId1, ['upc' => '123456']),
                'sku_for_my_invalid_upc'
            ),
            new ProductSubscriptionRequest(
                $productId2,
                $family,
                new ProductIdentifierValues($productId2, ['asin' => '654321']),
                'product_2'
            ),
        ];

        $collection = new ProductSubscriptionResponseCollection([
            42 => 'Invalid UPC: \'123456\'',
        ]);
        $collection->add(new ProductSubscriptionResponse($productId2, new SubscriptionId('abc-def-987'), [], false, false));

        $subscriptionProvider->bulkSubscribe($items)->willReturn($collection);

        $stepExecution->addWarning(
            'akeneo_franklin_insights.entity.product_subscription.constraint.invalid_mapped_values',
            [],
            new DataInvalidItem([
                'identifier' => 'sku_for_my_invalid_upc',
            ])
        )->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('subscribed')->shouldBeCalledTimes(1);
        $productSubscriptionRepository->save(Argument::type(ProductSubscription::class))->shouldBeCalledTimes(1);
        $eventDispatcher->dispatch(ProductSubscribed::EVENT_NAME, Argument::that(function ($productSubscribed) {
            return $productSubscribed instanceof ProductSubscribed &&
                'abc-def-987' === (string) $productSubscribed->getProductSubscription()->getSubscriptionId();
        }))->shouldBeCalled();

        $this->write($items)->shouldReturn(null);
    }
}
