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

use Akeneo\Pim\Automation\FranklinInsights\Application\Proposal\Command\CreateProposalsCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\Proposal\Command\CreateProposalsHandler;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

/**
 * Tasklet used to create proposals from data pulled from Franklin.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class CreateProposalsTasklet implements TaskletInterface
{
    /** @var CreateProposalsHandler */
    private $createProposalHandler;

    /** @var StepExecution */
    private $stepExecution;

    /**
     * @param CreateProposalsHandler $createProposalHandler
     */
    public function __construct(CreateProposalsHandler $createProposalHandler)
    {
        $this->createProposalHandler = $createProposalHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(): void
    {
        $command = new CreateProposalsCommand();
        $this->createProposalHandler->handle($command);
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }
}
