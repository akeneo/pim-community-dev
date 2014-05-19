<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Datagrid;

use PhpSpec\ObjectBehavior;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;

class ProposalGridHelperSpec extends ObjectBehavior
{
    function it_hides_approve_and_refuse_actions_if_the_proposal_status_is_not_empty(ResultRecordInterface $record)
    {
        $closure = $this->getActionConfigurationClosure();

        $record->getValue('status')->willReturn(1);
        $closure($record)->shouldReturn(['approve' => false, 'refuse' => false]);
    }

    function it_does_nothing_if_the_proposal_status_is_empty(ResultRecordInterface $record)
    {
        $closure = $this->getActionConfigurationClosure();

        $record->getValue('status')->willReturn(null);
        $closure($record)->shouldReturn(null);
    }
}
