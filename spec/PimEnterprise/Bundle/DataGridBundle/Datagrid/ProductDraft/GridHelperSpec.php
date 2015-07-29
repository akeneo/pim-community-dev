<?php

namespace spec\PimEnterprise\Bundle\DataGridBundle\Datagrid\ProductDraft;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class GridHelperSpec extends ObjectBehavior
{
    function let(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->beConstructedWith($authorizationChecker);
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
