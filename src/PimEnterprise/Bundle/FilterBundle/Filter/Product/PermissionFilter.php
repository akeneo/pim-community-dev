<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\FilterBundle\Filter\Product;

use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\ChoiceFilter as OroChoiceFilter;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use Pim\Bundle\CatalogBundle\Elasticsearch\SearchQueryBuilder;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Component\Security\Attributes;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

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

    /** @var CategoryAccessRepository $repository */
    protected $accessRepository;

    /**
     * @param FormFactoryInterface     $factory
     * @param FilterUtility            $util
     * @param TokenStorageInterface    $tokenStorage
     * @param CategoryAccessRepository $accessRepository
     */
    public function __construct(
        FormFactoryInterface $factory,
        FilterUtility $util,
        TokenStorageInterface $tokenStorage,
        CategoryAccessRepository $accessRepository
    ) {
        parent::__construct($factory, $util);

        $this->tokenStorage = $tokenStorage;
        $this->accessRepository = $accessRepository;
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
        $pqb = $ds->getProductQueryBuilder();
        $this->removeCategoryInListOrUnclassified($pqb);

        $grantedCategoryCodes = $this->accessRepository->getGrantedCategoryCodes($user, $level);
        if (count($grantedCategoryCodes) > 0) {
            $pqb->addFilter('categories', Operators::IN_LIST_OR_UNCLASSIFIED, $grantedCategoryCodes);
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
