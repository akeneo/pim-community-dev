<?php

namespace spec\PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\Proposal;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Bundle\UserBundle\Entity\UserInterface;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface;
use PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftRepositoryInterface;
use PimEnterprise\Component\Workflow\Provider\ProductDraftGrantedAttributeProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class GridHelperSpec extends ObjectBehavior
{
    function let(
        ProductDraftRepositoryInterface $repository,
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage,
        RequestStack $requestStack,
        ProductDraftGrantedAttributeProvider $attributeProvider
    ) {
        $this->beConstructedWith(
            $repository,
            $authorizationChecker,
            $tokenStorage,
            $requestStack,
            $attributeProvider
        );
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

    function it_provides_attribute_choices(
        $repository,
        $tokenStorage,
        $requestStack,
        $attributeProvider,
        TokenInterface $token,
        UserInterface $user,
        ProductDraftInterface $draft,
        AttributeInterface $name1,
        AttributeInterface $name2,
        AttributeInterface $price,
        AttributeGroupInterface $marketing,
        AttributeGroupInterface $general1,
        AttributeGroupInterface $general2
    ) {
        $token->getUser()->willReturn($user);
        $tokenStorage->getToken()->willReturn($token);
        $requestStack->getCurrentRequest()->willReturn(new Request());
        $repository->findApprovableByUserAndProductId($user, null)->willReturn([$draft]);
        $attributeProvider->getViewable($draft)->willReturn([$name1, $name2, $price]);

        $name1->getGroup()->willReturn($general1);
        $name1->getCode()->willReturn('name1');
        $name1->getLabel()->willReturn('Name');

        $name2->getGroup()->willReturn($general2);
        $name2->getCode()->willReturn('name2');
        $name2->getLabel()->willReturn('Name');

        $price->getGroup()->willReturn($marketing);
        $price->getCode()->willReturn('price');
        $price->getLabel()->willReturn('Price');

        $general1->getLabel()->willReturn('General');
        $general1->getCode()->willReturn('general1');

        $general2->getLabel()->willReturn('General');
        $general2->getCode()->willReturn('general2');

        $marketing->getLabel()->willReturn('Marketing');
        $marketing->getCode()->willReturn('marketing');

        $this->getAttributeChoices()->shouldReturn([
            'General (general1)' => ['name1' => 'Name'],
            'General (general2)' => ['name2' => 'Name'],
            'Marketing' => ['price' => 'Price'],
        ]);
    }

    function it_provides_attribute_choices_based_on_requested_product(
        $repository,
        $tokenStorage,
        $requestStack,
        TokenInterface $token,
        UserInterface $user
    )
    {
        $requestStack->getCurrentRequest()->willReturn(new Request(['params' => ['product' => '42']]));
        $token->getUser()->willReturn($user);
        $tokenStorage->getToken()->willReturn($token);

        $repository->findApprovableByUserAndProductId($user, '42')->shouldBeCalled()->willReturn([]);

        $this->getAttributeChoices()->shouldReturn([]);
    }
}
