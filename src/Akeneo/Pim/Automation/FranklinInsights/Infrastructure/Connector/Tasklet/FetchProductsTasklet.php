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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\Tasklet;

use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command\FetchProductsCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command\FetchProductsHandler;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Exception\ProductSubscriptionException;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

/**
 * Tasklet used to launch data pulling from Franklin.
 * The job it is used in can be run automatically with a CRON task,
 * but also manually from a specific date (TODO: APAI-170).
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class FetchProductsTasklet implements TaskletInterface
{
    /** @var FetchProductsHandler */
    private $fetchProductsHandler;

    /** @var StepExecution */
    private $stepExecution;

    /**
     * @param FetchProductsHandler $fetchProductsHandler
     */
    public function __construct(FetchProductsHandler $fetchProductsHandler)
    {
        $this->fetchProductsHandler = $fetchProductsHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(): void
    {
        try {
            $command = new FetchProductsCommand();
            $this->fetchProductsHandler->handle($command);
        } catch (ProductSubscriptionException $exception) {
            $this->stepExecution->addError($exception->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }
}
