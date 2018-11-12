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

use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderFactory;
use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderInterface;
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
        DataProviderFactory $dataProviderFactory,
        DataProviderInterface $dataProvider,
        ProductSubscriptionRepositoryInterface $productSubscriptionRepository,
        IdentifiersMappingRepositoryInterface $identifiersMappingRepository,
        StepExecution $stepExecution,
        IdentifiersMapping $identifiersMapping
    ): void {
        $dataProviderFactory->create()->willReturn($dataProvider);
        $identifiersMappingRepository->find()->willReturn($identifiersMapping);
        $this->beConstructedWith($dataProviderFactory, $productSubscriptionRepository, $identifiersMappingRepository);
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
        $dataProvider,
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

        $collection = new ProductSubscriptionResponseCollection();
        $collection->add(new ProductSubscriptionResponse(42, '123-465-789', [], false));
        $collection->add(new ProductSubscriptionResponse(50, 'abc-def-987', [], false));

        $dataProvider->bulkSubscribe($items)->willReturn($collection);

        $stepExecution->incrementSummaryInfo('subscribed')->shouldBeCalledTimes(2);
        $productSubscriptionRepository->save(Argument::type(ProductSubscription::class))->shouldBeCalledTimes(2);

        $this->write($items)->shouldReturn(null);
    }
}
