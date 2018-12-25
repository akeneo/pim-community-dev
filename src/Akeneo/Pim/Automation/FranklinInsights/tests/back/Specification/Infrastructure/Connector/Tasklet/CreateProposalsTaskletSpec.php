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

use Akeneo\Pim\Automation\FranklinInsights\Application\Proposal\Command\CreateProposalsCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\Proposal\Command\CreateProposalsHandler;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\Tasklet\CreateProposalsTasklet;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class CreateProposalsTaskletSpec extends ObjectBehavior
{
    public function let(
        StepExecution $stepExecution,
        CreateProposalsHandler $createProposalHandler
    ): void {
        $this->beConstructedWith($createProposalHandler);

        $this->setStepExecution($stepExecution);
    }

    public function it_is_a_tasklet_for_creating_proposals(): void
    {
        $this->shouldBeAnInstanceOf(CreateProposalsTasklet::class);
        $this->shouldImplement(TaskletInterface::class);
    }

    public function it_creates_proposals($createProposalHandler): void
    {
        $command = new CreateProposalsCommand();
        $createProposalHandler->handle($command)->shouldBeCalled();

        $this->execute();
    }
}
