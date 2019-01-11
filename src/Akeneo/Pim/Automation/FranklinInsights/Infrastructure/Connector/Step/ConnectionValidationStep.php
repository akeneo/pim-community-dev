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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\Step;

use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusQuery;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Exception\DataProviderException;
use Akeneo\Tool\Bundle\BatchBundle\Item\Validator\ValidationException;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\AbstractStep;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class ConnectionValidationStep extends AbstractStep
{
    /** @var GetConnectionStatusHandler */
    private $connectionStatusHandler;

    /**
     * @param $name
     * @param EventDispatcherInterface $eventDispatcher
     * @param JobRepositoryInterface $jobRepository
     * @param GetConnectionStatusHandler $connectionStatusHandler
     */
    public function __construct(
        $name,
        EventDispatcherInterface $eventDispatcher,
        JobRepositoryInterface $jobRepository,
        GetConnectionStatusHandler $connectionStatusHandler
    ) {
        parent::__construct($name, $eventDispatcher, $jobRepository);

        $this->connectionStatusHandler = $connectionStatusHandler;
    }

    /**
     * {@inheritdoc}
     */
    protected function doExecute(StepExecution $stepExecution): void
    {
        $connectionStatus = $this->connectionStatusHandler->handle(new GetConnectionStatusQuery(true));

        if (!$connectionStatus->isValid()) {
            throw new ValidationException(DataProviderException::authenticationError()->getMessage());
        }

        $stepExecution->addSummaryInfo('configuration_validation', 'OK');
    }
}
