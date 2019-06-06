<?php

namespace Specification\Akeneo\Pim\Permission\Bundle\Persistence\ORM\EntityWithValue;

use Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\Category\GetGrantedCategoryCodes;
use Akeneo\Pim\Permission\Bundle\Persistence\ORM\EntityWithValue\ProductQueryBuilderFactory;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;

class ProductQueryBuilderFactorySpec extends ObjectBehavior
{
    function let(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        TokenStorageInterface $tokenStorage,
        GetGrantedCategoryCodes $getAllGrantedCategoryCodes
    ) {
        $this->beConstructedWith($pqbFactory, $tokenStorage, $getAllGrantedCategoryCodes);
    }

    function it_implements_a_product_query_builder_factory_interface()
    {
        $this->shouldImplement(ProductQueryBuilderFactoryInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductQueryBuilderFactory::class);
    }

    function it_injects_granted_categories_in_pqb(
        $pqbFactory,
        $tokenStorage,
        GetGrantedCategoryCodes $getAllGrantedCategoryCodes,
        ProductQueryBuilderInterface $pqb,
        TokenInterface $token,
        UserInterface $user
    ) {
        $pqbFactory->create([])->willReturn($pqb);

        $categoryCodes = ['category_1', 'category_2'];
        $user->getId()->willReturn(1);

        $getAllGrantedCategoryCodes->forGroupIds([1,2,3,4])->willReturn($categoryCodes);

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $user->getGroupsIds()->willReturn([1,2,3,4]);

        $pqb->addFilter('categories', Operators::IN_LIST_OR_UNCLASSIFIED, $categoryCodes, ['type_checking' => false])->shouldBeCalled();
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
