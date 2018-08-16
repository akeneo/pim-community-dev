<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Bundle\Persistence\ORM\Category\Query;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Tool\Component\Classification\Model\CategoryInterface;
use Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface;
use Pim\Bundle\EnrichBundle\Doctrine\Counter\CategoryItemsCounterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Granted category product counter.
 * It extends the CategoryProductCounter of the CE to apply permissions.
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
class GrantedCategoryProductsCounter implements CategoryItemsCounterInterface
{
    /** @var ProductQueryBuilderFactoryInterface */
    private $pqbFactory;

    /** @var CategoryRepositoryInterface */
    private $categoryRepository;

    /** @var CategoryAccessRepository */
    private $categoryAccessRepo;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /**
     * @param ProductQueryBuilderFactoryInterface $pqbFactory
     * @param CategoryRepositoryInterface         $categoryRepository
     * @param CategoryAccessRepository            $categoryAccessRepo
     * @param AuthorizationCheckerInterface       $authorizationChecker
     * @param TokenStorageInterface               $tokenStorage
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        CategoryRepositoryInterface $categoryRepository,
        CategoryAccessRepository $categoryAccessRepo,
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage
    ) {
        $this->pqbFactory = $pqbFactory;
        $this->categoryRepository = $categoryRepository;
        $this->categoryAccessRepo = $categoryAccessRepo;
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     *
     * @see getItemsCountInCategory same logic with applied permissions
     */
    public function getItemsCountInCategory(CategoryInterface $category, $inChildren = false, $inProvided = true): int
    {
        if (!$this->authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $category)) {
            return 0;
        }

        if ($inChildren) {
            $categoryCodes = $this->categoryAccessRepo->getGrantedChildrenCodes(
                $category,
                $this->tokenStorage->getToken()->getUser(),
                Attributes::VIEW_ITEMS
            );
        } else {
            $categoryCodes = [$category->getCode()];
        }

        $options = [
            'filters' => [
                [
                    'field' => 'categories',
                    'operator' => Operators::IN_LIST,
                    'value' => $categoryCodes
                ]
            ]
        ];

        $pqb = $this->pqbFactory->create($options);
        $items = $pqb->execute();

        return $items->count();
    }
}
