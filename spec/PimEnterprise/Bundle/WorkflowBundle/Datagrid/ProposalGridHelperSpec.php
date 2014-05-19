<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Datagrid;

use PhpSpec\ObjectBehavior;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use PimEnterprise\Bundle\WorkflowBundle\Model\Proposal;

class ProposalGridHelperSpec extends ObjectBehavior
{
    function it_hides_approve_and_refuse_actions_if_the_proposal_status_is_not_waiting(ResultRecordInterface $record)
    {
        $closure = $this->getActionConfigurationClosure();

        $record->getValue('status')->willReturn('foo');
        $closure($record)->shouldReturn(['approve' => false, 'refuse' => false]);
    }

    function it_does_nothing_if_the_proposal_status_is_waiting(ResultRecordInterface $record)
    {
        $closure = $this->getActionConfigurationClosure();

        $record->getValue('status')->willReturn(Proposal::WAITING);
        $closure($record)->shouldReturn(null);
    }

    function it_provides_proposal_status_choices()
    {
        $this->getStatusChoices()->shouldReturn(
            [
                Proposal::WAITING  => 'pimee_workflow.proposal.status.waiting',
                Proposal::APPROVED => 'pimee_workflow.proposal.status.approved',
                Proposal::REFUSED  => 'pimee_workflow.proposal.status.refused'
            ]
        );
    }
}
