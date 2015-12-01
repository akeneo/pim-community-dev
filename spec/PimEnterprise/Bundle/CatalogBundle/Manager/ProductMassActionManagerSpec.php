<?php

namespace spec\PimEnterprise\Bundle\CatalogBundle\Manager;

use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductMassActionRepositoryInterface;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\AttributeGroupAccessRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ProductMassActionManagerSpec extends ObjectBehavior
{
    function let(
        ProductMassActionRepositoryInterface $massActionRepo,
        AttributeRepositoryInterface $attRepo,
        AttributeGroupAccessRepository $attGroupAccessRepo,
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
        UserInterface $user
    ) {
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $this->beConstructedWith($massActionRepo, $attRepo, $attGroupAccessRepo, $tokenStorage);
    }

    function it_finds_attributes_with_groups_with_sub_query(
        $massActionRepo,
        $attRepo,
        $attGroupAccessRepo,
        $user,
        QueryBuilder $subQB,
        ProductInterface $productOne,
        ProductInterface $productTwo
    ) {
        $products   = [$productOne, $productTwo];
        $attributeIds = [1, 2, 3, 4, 5];
        $productOne->getId()->willReturn(1);
        $productTwo->getId()->willReturn(2);

        $massActionRepo->findCommonAttributeIds([1, 2])->shouldBeCalled()->willReturn($attributeIds);

        $attGroupAccessRepo
            ->getGrantedAttributeGroupQB($user, Attributes::EDIT_ATTRIBUTES)
            ->shouldBeCalled()
            ->willReturn($subQB);

        $conditions = [
            'conditions' => ['unique' => 0],
            'filters'    => ['g.id'   => $subQB]
        ];
        $attRepo->findWithGroups($attributeIds, $conditions)->shouldBeCalled()->willReturn(['foo', 'bar']);

        $this->findCommonAttributes($products)->shouldReturn(['foo', 'bar']);
    }
}
