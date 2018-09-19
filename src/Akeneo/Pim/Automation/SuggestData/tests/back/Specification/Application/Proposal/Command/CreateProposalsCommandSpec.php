<?php

namespace Specification\Akeneo\Pim\Automation\SuggestData\Application\Proposal\Command;

use Akeneo\Pim\Automation\SuggestData\Application\Proposal\Command\CreateProposalsCommand;
use PhpSpec\ObjectBehavior;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class CreateProposalsCommandSpec extends ObjectBehavior
{
    public function it_is_a_create_proposal_command()
    {
        $this->shouldBeAnInstanceOf(CreateProposalsCommand::class);
    }
}
