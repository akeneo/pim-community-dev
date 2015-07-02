<?php

namespace spec\PimEnterprise\Bundle\DataGridBundle\Datagrid\ProductDraft;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

class GridHelperSpec extends ObjectBehavior
{
    function let(SecurityContextInterface $securityContext)
    {
        $this->beConstructedWith($securityContext);
    }

    function it_provides_product_draft_status_choices()
    {
        $this->getStatusChoices()->shouldReturn(
            [
                ProductDraftInterface::IN_PROGRESS => 'pimee_workflow.product_draft.status.in_progress',
                ProductDraftInterface::READY       => 'pimee_workflow.product_draft.status.ready',
            ]
        );
    }
}
