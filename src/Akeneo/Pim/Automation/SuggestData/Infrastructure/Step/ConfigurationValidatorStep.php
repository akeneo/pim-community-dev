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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Step;

use Akeneo\Pim\Automation\SuggestData\Application\Configuration\Service\GetSuggestDataConnectionStatus;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\IdentifiersMappingRepositoryInterface;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\AbstractStep;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class ConfigurationValidatorStep extends AbstractStep
{
    /** @var GetSuggestDataConnectionStatus */
    private $connectionStatus;

    /** @var IdentifiersMappingRepositoryInterface */
    private $identifiersMappingRepo;

    /**
     * @param $name
     * @param EventDispatcherInterface $eventDispatcher
     * @param JobRepositoryInterface $jobRepository
     * @param GetSuggestDataConnectionStatus $connectionStatus
     * @param IdentifiersMappingRepositoryInterface $identifiersMappingRepo
     */
    public function __construct(
        $name,
        EventDispatcherInterface $eventDispatcher,
        JobRepositoryInterface $jobRepository,
        GetSuggestDataConnectionStatus $connectionStatus,
        IdentifiersMappingRepositoryInterface $identifiersMappingRepo
    ) {
        parent::__construct($name, $eventDispatcher, $jobRepository);
        $this->connectionStatus = $connectionStatus;
        $this->identifiersMappingRepo = $identifiersMappingRepo;
    }

    /**
     * {@inheritdoc}
     */
    protected function doExecute(StepExecution $stepExecution): void
    {
        // TODO : the validation should be handled by specific services
        if (true !== $this->connectionStatus->isActive()) {
            throw new \Exception('Token is invalid or expired');
        }

        $mapping = $this->identifiersMappingRepo->find();
        if ($mapping->isEmpty()) {
            throw new \Exception('Identifiers mapping is empty');
        }

        $stepExecution->addSummaryInfo('configuration_validation', 'OK');
    }
}
