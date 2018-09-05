<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\SuggestData\Application\Proposal\Command;

use Akeneo\Pim\Automation\SuggestData\Application\Proposal\Command\CreateProposalCommand;
use Akeneo\Pim\Automation\SuggestData\Application\Proposal\Command\CreateProposalHandler;
use PhpSpec\ObjectBehavior;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class CreateProposalHandlerSpec extends ObjectBehavior
{
    function it_is_a_create_proposal_handler()
    {
        $this->shouldHaveType(CreateProposalHandler::class);
    }

    function it_handles_a_create_proposal_command(CreateProposalCommand $command)
    {
        $this->handle($command)->shouldReturn(null);
    }
}
