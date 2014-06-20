<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Datagrid;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\WorkflowBundle\Model\Proposition;

class PropositionGridHelperSpec extends ObjectBehavior
{
    function it_provides_proposition_status_choices()
    {
        $this->getStatusChoices()->shouldReturn(
            [
                Proposition::IN_PROGRESS => 'pimee_workflow.proposition.status.in_progress',
                Proposition::READY => 'pimee_workflow.proposition.status.ready',
            ]
        );
    }
}
