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
use Pim\Component\Catalog\Repository\ProductCategoryRepositoryInterface;
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

    /** @var ProductCategoryRepositoryInterface $repository */
    protected $productRepository;

    /** @var CategoryAccessRepository $repository */
    protected $accessRepository;

    /**
     * Constructor
     *
     * @param FormFactoryInterface               $factory
     * @param FilterUtility                      $util
     * @param TokenStorageInterface              $tokenStorage
     * @param ProductCategoryRepositoryInterface $productRepository
     * @param CategoryAccessRepository           $accessRepository
     */
    public function __construct(
        FormFactoryInterface $factory,
        FilterUtility $util,
        TokenStorageInterface $tokenStorage,
        ProductCategoryRepositoryInterface $productRepository,
        CategoryAccessRepository $accessRepository
    ) {
        parent::__construct($factory, $util);

        $this->tokenStorage = $tokenStorage;
        $this->productRepository = $productRepository;
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
        $qb = $ds->getQueryBuilder();

        $grantedCategoryIds = $this->accessRepository->getGrantedCategoryIds($user, $level);
        if (count($grantedCategoryIds) > 0) {
            $this->productRepository->applyFilterByCategoryIdsOrUnclassified($qb, $grantedCategoryIds);
        } else {
            $this->productRepository->applyFilterByUnclassified($qb);
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
}
