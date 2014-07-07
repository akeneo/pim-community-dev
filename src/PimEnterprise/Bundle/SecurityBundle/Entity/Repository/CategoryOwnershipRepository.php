<?php

namespace PimEnterprise\Bundle\SecurityBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\AbstractQuery;
use Oro\Bundle\UserBundle\Entity\Role;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * Category ownership repository
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class CategoryOwnershipRepository extends EntityRepository
{
    /**
     * Find category ownerships for a role
     * If tree is provided, only ownerships with category in this tree are returned
     *
     * @param Role                   $role
     * @param CategoryInterface|null $tree
     *
     * @return ArrayCollection
     */
    public function findForRole(Role $role, CategoryInterface $tree = null)
    {
        $qb = $this
            ->createQueryBuilder('o')
            ->where('o.role = :role')
            ->setParameter('role', $role);

        if ($tree) {
            $qb
                ->leftJoin('o.category', 'category')
                ->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->eq('category.id', ':treeId'),
                        $qb->expr()->eq('category.root', ':treeId')
                    )
                )
                ->setParameter('treeId', $tree->getId());
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param ProductInterface $product
     *
     * @return array
     */
    public function findRolesForProduct(ProductInterface $product)
    {
        $categories = $product->getCategories();
        if (count($categories) === 0) {
            return [];
        }
        $categoryIds = [];
        foreach ($categories as $category) {
            $categoryIds[]= $category->getId();
        }
        $qb = $this->createQueryBuilder('o');
        $qb->where($qb->expr()->in('o.category', $categoryIds));
        $qb->leftJoin('o.role', 'role');
        $qb->select('DISTINCT(role.id) as id, role.label');
        $roles = $qb->getQuery()->execute(array(), AbstractQuery::HYDRATE_ARRAY);

        return $roles;
    }

    /**
     * Indicates whether a user is the owner of any categories
     *
     * @param User $user
     *
     * @return boolean
     */
    public function isOwner(User $user)
    {
        $qb = $this->createQueryBuilder('o');

        $qb
            ->select('o.id')
            ->where(
                $qb->expr()->in('o.role', ':roles')
            )
            ->setParameter('roles', $user->getRoles())
            ->setMaxResults(1);

        $result = $qb->getQuery()->getResult(AbstractQuery::HYDRATE_SCALAR);

        return (bool) count($result);
    }
}
