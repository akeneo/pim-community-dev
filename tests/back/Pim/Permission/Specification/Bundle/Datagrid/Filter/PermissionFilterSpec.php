<?php

namespace Specification\Akeneo\Pim\Permission\Bundle\Datagrid\Filter;

use Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\Category\GetGrantedCategoryCodes;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use PhpSpec\ObjectBehavior;
use Oro\Bundle\PimFilterBundle\Datasource\Orm\OrmFilterProductDatasourceAdapter;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Prophecy\Argument;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class PermissionFilterSpec extends ObjectBehavior
{
    function let(
        FormFactoryInterface $factory,
        FilterUtility $utility,
        TokenInterface $token,
        UserInterface $user,
        TokenStorageInterface $tokenStorage,
        GetGrantedCategoryCodes $getAllViewableCategoryCodes,
        GetGrantedCategoryCodes $getAllOwnableCategoryCodes,
        GetGrantedCategoryCodes $getAllEditableCategoryCodes
    ) {
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $user->getGroupsIds()->willReturn([1,2]);

        $this->beConstructedWith($factory, $utility, $tokenStorage, $getAllViewableCategoryCodes, $getAllOwnableCategoryCodes, $getAllEditableCategoryCodes);
    }

    function it_applies_a_filter_on_owned_products_with_granted_categories(
        GetGrantedCategoryCodes $getAllOwnableCategoryCodes,
        ProductQueryBuilderInterface $pqb,
        OrmFilterProductDatasourceAdapter $datasource
    ) {
        $getAllOwnableCategoryCodes->forGroupIds([1,2])->willReturn(['bar', 'baz']);
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
            ['bar', 'baz'],
            ['type_checking' => false]
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
        GetGrantedCategoryCodes $getAllOwnableCategoryCodes,
        ProductQueryBuilderInterface $pqb,
        OrmFilterProductDatasourceAdapter $datasource
    ) {
        $getAllOwnableCategoryCodes->forGroupIds([1,2])->willReturn([]);
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
