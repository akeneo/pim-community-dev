<?php

namespace spec\PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\Proposal;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\Proposal\GridHelper;
use PimEnterprise\Bundle\WorkflowBundle\Helper\ProductDraftChangesPermissionHelper;
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

interface ProductSpecInterface extends EntityWithFamilyVariantInterface, ProductInterface
{
}
