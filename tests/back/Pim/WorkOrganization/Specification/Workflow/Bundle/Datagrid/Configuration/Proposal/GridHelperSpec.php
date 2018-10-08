<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Configuration\Proposal;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Configuration\Proposal\GridHelper;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Helper\ProductDraftChangesPermissionHelper;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class GridHelperSpec extends ObjectBehavior
{
    function let(
        AuthorizationCheckerInterface $authorizationChecker,
        ProductDraftChangesPermissionHelper $permissionHelper
    ) {
        $this->beConstructedWith(
            $authorizationChecker,
            $permissionHelper
        );
    }

    function it_should_be_a_grid_helper()
    {
        $this->shouldBeAnInstanceOf(GridHelper::class);
    }
}
