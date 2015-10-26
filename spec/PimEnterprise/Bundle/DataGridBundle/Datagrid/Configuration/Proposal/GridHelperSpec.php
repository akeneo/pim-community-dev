<?php

namespace spec\PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\Proposal;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\UserBundle\Entity\UserInterface;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface;
use PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftRepositoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class GridHelperSpec extends ObjectBehavior
{
    function let(
        ProductDraftRepositoryInterface $repository,
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage
    ) {
        $this->beConstructedWith($repository, $authorizationChecker, $tokenStorage);
    }

    function it_provides_proposal_author_choices($repository)
    {
        $repository->getDistinctAuthors()->willReturn(['bar', 'foo']);

        $this->getAuthorChoices()->shouldReturn(
            [
                'bar' => 'bar',
                'foo' => 'foo'
            ]
        );
    }

    function it_provides_proposal_product_choices(
        $repository,
        $tokenStorage,
        TokenInterface $token,
        UserInterface $user,
        ProductDraftInterface $draft1,
        ProductDraftInterface $draft2,
        ProductDraftInterface $draft3,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductInterface $product3
    ) {
        $token->getUser()->willReturn($user);
        $tokenStorage->getToken()->willReturn($token);

        $product1->getId()->willReturn(144);
        $product2->getId()->willReturn(42);
        $product3->getId()->willReturn(144);

        $product1->getLabel()->willReturn('Ice sword');
        $product2->getLabel()->willReturn('Warblade');
        $product3->getLabel()->willReturn('Ice sword');

        $draft1->getProduct()->willReturn($product1);
        $draft2->getProduct()->willReturn($product2);
        $draft3->getProduct()->willReturn($product3);

        $repository->findApprovableByUser($user)->willReturn([
            $draft1,
            $draft2,
            $draft3
        ]);

        $this->getProductChoices()->shouldReturn([
            '144' => 'Ice sword',
            '42'  => 'Warblade'
        ]);
    }
}
