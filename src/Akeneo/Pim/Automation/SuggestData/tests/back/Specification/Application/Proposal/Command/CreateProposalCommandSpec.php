<?php

namespace Specification\Akeneo\Pim\Automation\SuggestData\Application\Proposal\Command;

use Akeneo\Pim\Automation\SuggestData\Application\Proposal\Command\CreateProposalCommand;
use PhpSpec\ObjectBehavior;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class CreateProposalCommandSpec extends ObjectBehavior
{
    public function it_is_a_create_proposal_command()
    {
        $this->shouldBeAnInstanceOf(CreateProposalCommand::class);
    }
}
