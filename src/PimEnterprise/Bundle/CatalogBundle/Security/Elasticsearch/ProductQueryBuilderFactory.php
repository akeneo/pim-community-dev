<?php

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
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
    public function create(array $options = [])
    {
        $pqb = $this->pqbFactory->create($options);

        if (null === $this->tokenStorage->getToken()) {
            throw new \LogicException('The token cannot be null on the instantiation of the Product Query Builder.');
        }

        $grantedCategories = $this->categoryAccessRepository->getGrantedCategoryCodes(
            $this->tokenStorage->getToken()->getUser(),
            Attributes::VIEW_ITEMS
        );

        $pqb->addFilter('categories', Operators::IN_LIST_OR_UNCLASSIFIED, $grantedCategories);

        return $pqb;
    }
}
