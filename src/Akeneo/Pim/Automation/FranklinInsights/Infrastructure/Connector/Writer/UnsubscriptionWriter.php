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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\Writer;

use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command\UnsubscribeProductCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command\UnsubscribeProductHandler;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\ProductId;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Exception\ProductNotSubscribedException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Exception\ProductSubscriptionException;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class UnsubscriptionWriter implements ItemWriterInterface, StepExecutionAwareInterface
{
    /** @var UnsubscribeProductHandler */
    private $unsubscribeHandler;

    /** @var StepExecution */
    private $stepExecution;

    /**
     * @param UnsubscribeProductHandler $unsubscribeHandler
     */
    public function __construct(UnsubscribeProductHandler $unsubscribeHandler)
    {
        $this->unsubscribeHandler = $unsubscribeHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * {@inheritdoc}
     *
     * As there is no endpoint for bulk unsubscription, the batch size MUST be 1 (meaning
     * there should only be one product in the $items argument); in the opposite case,
     * throwing an InvalidItemException would skip the remaining items in the batch
     */
    public function write(array $items): void
    {
        foreach ($items as $item) {
            try {
                $productId = new ProductId($item->getId());
                $this->unsubscribeHandler->handle(new UnsubscribeProductCommand($productId));
                $this->stepExecution->incrementSummaryInfo('unsubscribed');
            } catch (ProductNotSubscribedException $e) {
                $this->stepExecution->incrementSummaryInfo('unsubscription_skipped_not_subscribed');
            } catch (ProductSubscriptionException $e) {
                throw new InvalidItemException(
                    sprintf('Could not unsubscribe product: %s', $e->getMessage()),
                    new DataInvalidItem(['identifier' => $item->getIdentifier()]),
                    [],
                    0,
                    $e
                );
            }
        }
    }
}
