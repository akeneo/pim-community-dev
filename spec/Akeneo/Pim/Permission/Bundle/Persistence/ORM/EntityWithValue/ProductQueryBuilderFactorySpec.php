<?php

namespace spec\Akeneo\Pim\Permission\Bundle\Persistence\ORM\EntityWithValue;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository;
use Akeneo\Pim\Permission\Component\Attributes;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ProductQueryBuilderFactorySpec extends ObjectBehavior
{
    function let(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        TokenStorageInterface $tokenStorage,
        CategoryAccessRepository $categoryAccessRepository
    ) {
        $this->beConstructedWith($pqbFactory, $tokenStorage, $categoryAccessRepository, 'VIEW_ITEMS');
    }

    function it_implements_a_product_query_builder_factory_interface()
    {
        $this->shouldImplement(ProductQueryBuilderFactoryInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(
            'Akeneo\Pim\Permission\Bundle\Persistence\ORM\EntityWithValue\ProductQueryBuilderFactory'
        );
    }

    function it_injects_granted_categories_in_pqb(
        $pqbFactory,
        $tokenStorage,
        $categoryAccessRepository,
        ProductQueryBuilderInterface $pqb,
        TokenInterface $token,
        UserInterface $user
    ) {
        $pqbFactory->create([])->willReturn($pqb);

        $categoryCodes = ['category_1', 'category_2'];
        $categoryAccessRepository->getGrantedCategoryCodes($user, Attributes::VIEW_ITEMS)->willReturn($categoryCodes);

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $pqb->addFilter('categories', Operators::IN_LIST_OR_UNCLASSIFIED, $categoryCodes)->shouldBeCalled();
        $this->create([])->shouldReturn($pqb);
    }

    function it_throws_an_exception_if_token_is_not_found($tokenStorage)
    {
        $tokenStorage->getToken()->willReturn(null);

        $this->shouldThrow(
            new \LogicException('Token cannot be null on the instantiation of the Product Query Builder.')
        )->during('create');
    }
}
