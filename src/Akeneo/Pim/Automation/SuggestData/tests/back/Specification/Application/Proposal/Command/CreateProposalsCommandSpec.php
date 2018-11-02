<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\SuggestData\Application\Proposal\Command;

use Akeneo\Pim\Automation\SuggestData\Application\Proposal\Command\CreateProposalsCommand;
use PhpSpec\ObjectBehavior;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class CreateProposalsCommandSpec extends ObjectBehavior
{
    public function it_is_a_create_proposal_command(): void
    {
        $this->beConstructedWith(100);
        $this->shouldBeAnInstanceOf(CreateProposalsCommand::class);
    }

    public function it_exposes_batch_size(): void
    {
        $this->beConstructedWith(42);
        $this->batchSize()->shouldReturn(42);
    }

    public function it_throws_an_excetion_if_batch_size_is_zero(): void
    {
        $this->beConstructedWith(0);
        $this->shouldThrow(new \InvalidArgumentException('Batch size must be positive'))->duringInstantiation();
    }

    public function it_throws_an_excetion_if_batch_size_is_negative(): void
    {
        $this->beConstructedWith(-100);
        $this->shouldThrow(new \InvalidArgumentException('Batch size must be positive'))->duringInstantiation();
    }
}
