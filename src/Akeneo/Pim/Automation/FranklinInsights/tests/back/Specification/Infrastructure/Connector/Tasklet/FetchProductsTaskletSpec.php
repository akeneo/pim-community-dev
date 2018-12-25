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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\Tasklet;

use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command\FetchProductsCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command\FetchProductsHandler;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Exception\ProductSubscriptionException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\Tasklet\FetchProductsTasklet;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class FetchProductsTaskletSpec extends ObjectBehavior
{
    public function let(
        StepExecution $stepExecution,
        FetchProductsHandler $fetchProductsHandler
    ): void {
        $this->beConstructedWith($fetchProductsHandler);

        $this->setStepExecution($stepExecution);
    }

    public function it_is_a_tasklet_for_fetching_product_data_from_franklin(): void
    {
        $this->shouldBeAnInstanceOf(FetchProductsTasklet::class);
        $this->shouldImplement(TaskletInterface::class);
    }

    public function it_fetches_products($stepExecution, $fetchProductsHandler): void
    {
        $command = new FetchProductsCommand();
        $fetchProductsHandler->handle($command)->shouldBeCalled();

        $stepExecution->addError(Argument::any())->shouldNotBeCalled();

        $this->execute();
    }

    public function it_handles_product_subscription_exception($stepExecution, $fetchProductsHandler): void
    {
        $exception = new ProductSubscriptionException('Whatever reason pulling from Franklin failed');

        $command = new FetchProductsCommand();
        $fetchProductsHandler->handle($command)->willThrow($exception);

        $stepExecution->addError('Whatever reason pulling from Franklin failed')->shouldBeCalled();

        $this->execute();
    }
}
