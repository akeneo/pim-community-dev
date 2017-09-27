<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr\Join;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\ProductMassActionRepository as BaseProductMassActionRepository;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\User\Model\GroupInterface;
use PimEnterprise\Bundle\WorkflowBundle\Doctrine\ORM\Repository\PublishedProductRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Overriden product mass action repository to apply permissions
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class ProductMassActionRepository extends BaseProductMassActionRepository
{
    /** @var PublishedProductRepository */
    protected $publishedRepository;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var string */
    protected $categoryAccessClass;

    /**
     * @param EntityManager              $em
     * @param string                     $entityName
     * @param PublishedProductRepository $publishedRepository
     * @param TokenStorageInterface      $tokenStorage
     * @param string                     $categoryAccessClass
     * @param string                     $productClass
     */
    public function __construct(
        EntityManager $em,
        $entityName,
        PublishedProductRepository $publishedRepository,
        TokenStorageInterface $tokenStorage,
        $categoryAccessClass
    ) {
        parent::__construct($em, $entityName);

        $this->publishedRepository = $publishedRepository;
        $this->tokenStorage = $tokenStorage;
        $this->categoryAccessClass = $categoryAccessClass;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteFromIds(array $ids)
    {
        $publishedIds = $this->publishedRepository->getProductIdsMapping($ids);
        if (!empty($publishedIds)) {
            throw new \Exception(
                'Impossible to mass delete products. You should not have any published products in your selection.'
            );
        }

        $grantedIds = $this->getProductIdsOwned($ids);
        $notGrantedIds = array_diff($ids, $grantedIds);
        if (!empty($notGrantedIds)) {
            throw new \Exception(
                'Impossible to mass delete products. To be deleted, all the products of your selection ' .
                'should be categorized in at least one category that you own.'
            );
        }

        return parent::deleteFromIds($ids);
    }

    /**
     * Retrieves ids of products owned by the user among given product ids.
     * A product is owned if the user belongs to a group which has permission to own at least one category of
     * the product or the product is not categorized.
     *
     * @param array $productIds
     *
     * @return array
     */
    protected function getProductIdsOwned(array $productIds): array
    {
        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('product.id')->distinct(true)
            ->from(ProductInterface::class, 'product')
            ->leftJoin('product.categories', 'prodCategory')
            ->leftJoin(
                $this->categoryAccessClass,
                'catAccess',
                Join::WITH,
                'catAccess.category = prodCategory.id'
            )
            ->where($qb->expr()->in('product.id', ':ids'))
            ->setParameter('ids', $productIds)
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->isNull('prodCategory.id'),
                    $qb->expr()->andX(
                        $qb->expr()->eq('catAccess.ownItems', true),
                        $qb->expr()->in('catAccess.userGroup', ':userGroupIds')
                    )
                )
            )
            ->setParameter('userGroupIds', $this->getCurrentUserGroupIds());

        return array_map(
            function ($row) {
                return (int) $row['id'];
            },
            $qb->getQuery()->getScalarResult()
        );
    }

    /**
     * Returns current user group ids
     *
     * @return array
     */
    protected function getCurrentUserGroupIds(): array
    {
        $user = $this->tokenStorage->getToken()->getUser();

        return array_map(
            function (GroupInterface $group) {
                return $group->getId();
            },
            $user->getGroups()->toArray()
        );
    }
}
