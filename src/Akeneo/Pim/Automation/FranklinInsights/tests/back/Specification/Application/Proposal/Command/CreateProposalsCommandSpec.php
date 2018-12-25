<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Application\Proposal\Command;

use Akeneo\Pim\Automation\FranklinInsights\Application\Proposal\Command\CreateProposalsCommand;
use PhpSpec\ObjectBehavior;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class CreateProposalsCommandSpec extends ObjectBehavior
{
    public function it_is_a_create_proposal_command(): void
    {
        $this->shouldBeAnInstanceOf(CreateProposalsCommand::class);
    }
}
