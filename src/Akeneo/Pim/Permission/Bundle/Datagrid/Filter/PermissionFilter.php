<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Bundle\Datagrid\Filter;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\Category\GetGrantedCategoryCodes;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\ChoiceFilter as OroChoiceFilter;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use Oro\Bundle\PimFilterBundle\Datasource\FilterProductDatasourceAdapterInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Webmozart\Assert\Assert;

/**
 * Allow to know if current user can review/publish, edit or view products
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class PermissionFilter extends OroChoiceFilter
{
    /** @staticvar string */
    const OWN = 3;

    /** @staticvar string */
    const EDIT = 2;

    /** @staticvar string */
    const VIEW = 1;

    /* @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var GetGrantedCategoryCodes */
    private $getAllViewableCategoryCodes;

    /** @var GetGrantedCategoryCodes */
    private $getAllOwnableCategoryCodes;

    /** @var GetGrantedCategoryCodes */
    private $getAllEditableCategoryCodes;

    public function __construct(
        FormFactoryInterface $factory,
        FilterUtility $util,
        TokenStorageInterface $tokenStorage,
        GetGrantedCategoryCodes $getAllViewableCategoryCodes,
        GetGrantedCategoryCodes $getAllOwnableCategoryCodes,
        GetGrantedCategoryCodes $getAllEditableCategoryCodes
    ) {
        parent::__construct($factory, $util);

        $this->tokenStorage = $tokenStorage;
        $this->getAllViewableCategoryCodes = $getAllViewableCategoryCodes;
        $this->getAllOwnableCategoryCodes = $getAllOwnableCategoryCodes;
        $this->getAllEditableCategoryCodes = $getAllEditableCategoryCodes;
    }

    /**
     * Filter by permissions on category ids (category with owner permissions or not classified at all)
     *
     * {@inheritdoc}
     */
    public function apply(FilterDatasourceAdapterInterface $ds, $data)
    {
        $data = $this->parseData($data);
        if (!$data) {
            return false;
        }

        $level = $data['type'];
        $user = $this->tokenStorage->getToken()->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        Assert::implementsInterface($ds, FilterProductDatasourceAdapterInterface::class);
        $pqb = $ds->getProductQueryBuilder();
        $this->removeCategoryInListOrUnclassified($pqb);
        $userGroupIds = $user->getGroupsIds();

        $grantedCategoryCodes = [];

        switch ($level) {
            case Attributes::OWN_PRODUCTS:
                $grantedCategoryCodes = $this->getAllOwnableCategoryCodes->forGroupIds($userGroupIds);
                break;
            case Attributes::EDIT_ITEMS:
                $grantedCategoryCodes = $this->getAllEditableCategoryCodes->forGroupIds($userGroupIds);
                break;
            case Attributes::VIEW_ITEMS:
                $grantedCategoryCodes = $this->getAllViewableCategoryCodes->forGroupIds($userGroupIds);
                break;
        }

        if (count($grantedCategoryCodes) > 0) {
            $pqb->addFilter('categories', Operators::IN_LIST_OR_UNCLASSIFIED, $grantedCategoryCodes, ['type_checking' => false]);
        } else {
            $pqb->addFilter('categories', Operators::UNCLASSIFIED, '');
        }

        return true;
    }

    /**
     * @param array $data
     *
     * @return array|false
     */
    protected function parseData($data)
    {
        $mapping = [
            self::OWN  => Attributes::OWN_PRODUCTS,
            self::EDIT => Attributes::EDIT_ITEMS,
            self::VIEW => Attributes::VIEW_ITEMS
        ];

        if (!isset($data['value'])) {
            return false;
        }

        if (!isset($mapping[$data['value']])) {
            return false;
        }

        $data['type'] = $mapping[$data['value']];

        return $data;
    }

    /**
     * Removes previous filter clauses on categories.
     *
     * They can already be filter clauses on categories to prevent users to see
     * products they're not allowed to. The permission filter will add more
     * restrictive clauses.
     * However, category clauses "IN_LIST_OR_UNCLASSIFIED" is an Elasticsearch
     * "should" clauses (basically a "OR"), so if several are added, the less
     * restrictive one will win. As a result, the permission filter, which is
     * more restrictive, is never applied.
     *
     * @param ProductQueryBuilderInterface $pqb
     */
    protected function removeCategoryInListOrUnclassified(ProductQueryBuilderInterface $pqb)
    {
        $rawFilters = $pqb->getRawFilters();

        $pqb->setQueryBuilder(new SearchQueryBuilder());
        foreach ($rawFilters as $filter) {
            if ('categories' !== $filter['field'] && Operators::IN_LIST_OR_UNCLASSIFIED !== $filter['operator']) {
                $pqb->addFilter($filter['field'], $filter['operator'], $filter['value'], $filter['context']);
            }
        }
    }
}
