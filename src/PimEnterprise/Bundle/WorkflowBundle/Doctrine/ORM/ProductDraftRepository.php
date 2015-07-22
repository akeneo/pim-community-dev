<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Doctrine\ORM;

use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\UserBundle\Entity\User;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\WorkflowBundle\Doctrine\Repository;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraft;
use PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftOwnershipRepositoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftRepositoryInterface;

/**
 * ProductDraft ORM repository
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 */
class ProductDraftRepository extends EntityRepository implements
    ProductDraftRepositoryInterface,
    ProductDraftOwnershipRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findUserProductDraft(ProductInterface $product, $username)
    {
        return $this->findOneBy(
            [
                'product' => $product,
                'author'  => $username,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function findByProduct(ProductInterface $product)
    {
        return $this->findBy(['product' => $product]);
    }

    /**
     * {@inheritdoc}
     */
    public function findApprovableByUser(UserInterface $user, $limit = null)
    {
        $qb = $this->createQueryBuilder('p');

        $qb
            ->join('p.product', 'product')
            ->leftJoin('product.categories', 'category')
            ->innerJoin('PimEnterpriseSecurityBundle:CategoryAccess', 'a', 'WITH', 'a.category = category')
            ->where(
                $qb->expr()->eq('a.ownProducts', true)
            )
            ->andWhere(
                $qb->expr()->in('a.userGroup', ':userGroups')
            )
            ->andWhere(
                $qb->expr()->eq('p.status', ProductDraft::READY)
            )
            ->orderBy('p.createdAt', 'desc')
            ->setParameter('userGroups', $user->getGroups()->toArray())
            ->groupBy('p.id');

        if (null !== $limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function createDatagridQueryBuilder(array $parameters = [])
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('p, p.createdAt as createdAt, p.changes as changes, p.author as author, p.status as status')
            ->from($this->_entityName, 'p', 'p.id');

        if (isset($parameters['product'])) {
            $this->applyDatagridContext($qb, $parameters['product']);
        }

        return $qb;
    }

    /**
     * {@inheritdoc}
     *
     * @param \Doctrine\ORM\QueryBuilder
     */
    public function applyDatagridContext($qb, $productId)
    {
        $qb->innerJoin('p.product', 'product', 'WITH', 'product.id = :product');
        $qb->setParameter('product', $productId);

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param \Doctrine\ORM\QueryBuilder $qb
     */
    public function applyFilter($qb, $field, $operator, $value)
    {
        if ('IN' === $operator) {
            if (!empty($value)) {
                $fieldName = $this->getRootFieldName($qb, $field);
                $qb->andWhere($qb->expr()->in($fieldName, $value));
            }
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param \Doctrine\ORM\QueryBuilder $qb
     */
    public function applySorter($qb, $field, $direction)
    {
        $fieldName = $this->getRootFieldName($qb, $field);
        $qb->orderBy($fieldName, $direction);
    }

    /**
     * Build field name with root alias
     *
     * @param QueryBuilder $qb
     * @param string       $field
     *
     * @return string
     */
    protected function getRootFieldName(QueryBuilder $qb, $field)
    {
        return sprintf("%s.%s", current($qb->getRootAliases()), $field);
    }
}
