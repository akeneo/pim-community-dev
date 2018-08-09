<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Bundle\Persistence\ORM\EntityWithValue;

use Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Creates and configures the product query builder filtered by granted categories
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class ProductQueryBuilderFactory implements ProductQueryBuilderFactoryInterface
{
    /** @var string */
    private $accessLevel;

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
     * @param string                              $accessLevel
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        TokenStorageInterface $tokenStorage,
        CategoryAccessRepository $categoryAccessRepository,
        string $accessLevel
    ) {
        $this->pqbFactory = $pqbFactory;
        $this->tokenStorage = $tokenStorage;
        $this->categoryAccessRepository = $categoryAccessRepository;
        $this->accessLevel = $accessLevel;
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
            $this->accessLevel
        );

        $pqb->addFilter('categories', Operators::IN_LIST_OR_UNCLASSIFIED, $grantedCategories);

        return $pqb;
    }
}
