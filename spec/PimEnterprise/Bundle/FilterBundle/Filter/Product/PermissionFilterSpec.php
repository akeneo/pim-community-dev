<?php

namespace spec\PimEnterprise\Bundle\FilterBundle\Filter\Product;

use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\FilterBundle\Datasource\Orm\OrmFilterProductDatasourceAdapter;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use PimEnterprise\Component\Security\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use Prophecy\Argument;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class PermissionFilterSpec extends ObjectBehavior
{
    function let(
        FormFactoryInterface $factory,
        FilterUtility $utility,
        CategoryAccessRepository $accessRepository,
        TokenInterface $token,
        UserInterface $user,
        TokenStorageInterface $tokenStorage
    ) {
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $this->beConstructedWith($factory, $utility, $tokenStorage, $accessRepository);
    }

    function it_applies_a_filter_on_owned_products_with_granted_categories(
        $accessRepository,
        $user,
        ProductQueryBuilderInterface $pqb,
        OrmFilterProductDatasourceAdapter $datasource
    ) {
        $accessRepository->getGrantedCategoryCodes($user, Attributes::OWN_PRODUCTS)->willReturn(['bar', 'baz']);
        $datasource->getProductQueryBuilder()->willReturn($pqb);

        $pqb->getRawFilters()->willReturn([[
            'field'    => 'categories',
            'operator' => Operators::IN_LIST_OR_UNCLASSIFIED,
            'value'    => ['foobar'],
            'context'  => [],
        ]]);
        $pqb->setQueryBuilder(Argument::any())->shouldBeCalled();
        $pqb->addFilter('categories', Operators::IN_LIST_OR_UNCLASSIFIED, ['foo'], [])->shouldNotBeCalled();

        $pqb->addFilter(
            'categories',
            Operators::IN_LIST_OR_UNCLASSIFIED,
            ['bar', 'baz']
        )->shouldBeCalled();

        $this->apply(
            $datasource,
            [
                'value' => 3,
                'type' => null
            ]
        )->shouldReturn(true);
    }

    function it_applies_a_filter_on_owned_products_without_granted_categories(
        $accessRepository,
        $user,
        ProductQueryBuilderInterface $pqb,
        OrmFilterProductDatasourceAdapter $datasource
    ) {
        $accessRepository->getGrantedCategoryCodes($user, Attributes::OWN_PRODUCTS)->willReturn([]);
        $datasource->getProductQueryBuilder()->willReturn($pqb);

        $pqb->getRawFilters()->willReturn([[
            'field'    => 'categories',
            'operator' => Operators::IN_LIST_OR_UNCLASSIFIED,
            'value'    => ['foobar'],
            'context'  => [],
        ]]);
        $pqb->setQueryBuilder(Argument::any())->shouldBeCalled();
        $pqb->addFilter('categories', Operators::IN_LIST_OR_UNCLASSIFIED, ['foo'], [])->shouldNotBeCalled();

        $pqb->addFilter(
            'categories',
            Operators::UNCLASSIFIED,
            ''
        )->shouldBeCalled();

        $this->apply(
            $datasource,
            [
                'value' => 3,
                'type' => null
            ]
        )->shouldReturn(true);
    }
}
