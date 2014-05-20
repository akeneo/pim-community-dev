<?php

namespace spec\PimEnterprise\Bundle\CatalogBundle\Manager;

use PhpSpec\ObjectBehavior;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\UserBundle\Entity\User;
use Pim\Bundle\CatalogBundle\Repository\ProductMassActionRepositoryInterface;
use PimEnterprise\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\AttributeGroupAccessRepository;
use PimEnterprise\Bundle\SecurityBundle\SecurityContext;

class ProductMassActionManagerSpec extends ObjectBehavior
{
    function let(
        ProductMassActionRepositoryInterface $massActionRepo,
        AttributeRepository $attRepo,
        AttributeGroupAccessRepository $attGroupAccessRepo,
        SecurityContext $securityContext,
        User $user
    ) {
        $securityContext->getUser()->willReturn($user);

        $this->beConstructedWith($massActionRepo, $attRepo, $attGroupAccessRepo, $securityContext);
    }

    function it_should_find_attribute_with_groups_with_sub_query(
        $massActionRepo,
        $attRepo,
        $attGroupAccessRepo,
        $user,
        QueryBuilder $subQB
    ) {
        $productIds   = [1, 2];
        $attributeIds = [1, 2, 3, 4, 5];

        $massActionRepo->findCommonAttributeIds($productIds)->shouldBeCalled()->willReturn($attributeIds);

        $attGroupAccessRepo->getGrantedAttributeGroupQB($user, 'EDIT')->shouldBeCalled()->willReturn($subQB);

        $conditions = [
            'conditions' => ['unique' => 0],
            'filters'    => ['g.id'   => $subQB]
        ];
        $attRepo->findWithGroups($attributeIds, $conditions)->shouldBeCalled()->willReturn(['foo', 'bar']);

        $this->findCommonAttributes($productIds)->shouldReturn(['foo', 'bar']);
    }
}
