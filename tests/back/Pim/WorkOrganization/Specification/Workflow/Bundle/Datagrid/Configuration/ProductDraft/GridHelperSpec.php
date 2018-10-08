<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Configuration\ProductDraft;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Helper\ProductDraftChangesPermissionHelper;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class GridHelperSpec extends ObjectBehavior
{
    function let(
        AuthorizationCheckerInterface $authorizationChecker,
        ProductDraftChangesPermissionHelper $permissionHelper
    ) {
        $this->beConstructedWith($authorizationChecker, $permissionHelper);
    }

    function it_provides_product_draft_status_choices()
    {
        $this->getStatusChoices()->shouldReturn(
            [
                EntityWithValuesDraftInterface::IN_PROGRESS => 'pimee_workflow.product_draft.status.in_progress',
                EntityWithValuesDraftInterface::READY       => 'pimee_workflow.product_draft.status.ready',
            ]
        );
    }
}
