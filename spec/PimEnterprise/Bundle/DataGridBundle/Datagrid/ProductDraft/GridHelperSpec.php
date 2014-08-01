<?php

namespace spec\PimEnterprise\Bundle\DataGridBundle\Datagrid\ProductDraft;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraft;

class GridHelperSpec extends ObjectBehavior
{
    function it_provides_product_draft_status_choices()
    {
        $this->getStatusChoices()->shouldReturn(
            [
                ProductDraft::IN_PROGRESS => 'pimee_workflow.product_draft.status.in_progress',
                ProductDraft::READY => 'pimee_workflow.product_draft.status.ready',
            ]
        );
    }
}
