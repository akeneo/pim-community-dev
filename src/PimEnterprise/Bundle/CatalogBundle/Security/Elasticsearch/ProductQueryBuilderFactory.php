<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogBundle\Security\Elasticsearch;

use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Component\Security\Attributes;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Creates and configures the product query builder filtered by granted categories
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class ProductQueryBuilderFactory implements ProductQueryBuilderFactoryInterface
{
    /** @var ProductQueryBuilderFactoryInterface */
    private $pqbFactory;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var CategoryAccessRepository */
    private $categoryAccessRepository;

    /**
     * @param ProductQueryBuilderFactoryInterface $pqbFactory
     * @param TokenStorageInterface               $tokenStorage
     * @param CategoryAccessRepository            $categoryAccessRepository
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        TokenStorageInterface $tokenStorage,
        CategoryAccessRepository $categoryAccessRepository
    ) {
        $this->pqbFactory = $pqbFactory;
        $this->tokenStorage = $tokenStorage;
        $this->categoryAccessRepository = $categoryAccessRepository;
    }

    /**
     * Create a product query builder with only granted categories
     *
     * @param array $options
     *
     * @return ProductQueryBuilderInterface
     */
    public function create(array $options = []): ProductQueryBuilderInterface
    {
        $token = $this->tokenStorage->getToken();
        if (null === $token) {
            throw new \LogicException('Token cannot be null on the instantiation of the Product Query Builder.');
        }

        $pqb = $this->pqbFactory->create($options);

        $grantedCategories = $this->categoryAccessRepository->getGrantedCategoryCodes(
            $token->getUser(),
            Attributes::VIEW_ITEMS
        );

        $pqb->addFilter('categories', Operators::IN_LIST_OR_UNCLASSIFIED, $grantedCategories);

        return $pqb;
    }
}
