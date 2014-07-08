<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Doctrine\ORM;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\UserBundle\Entity\User;
use PimEnterprise\Bundle\WorkflowBundle\Doctrine\Repository;
use PimEnterprise\Bundle\WorkflowBundle\Model\Proposition;
use PimEnterprise\Bundle\WorkflowBundle\Repository\PropositionRepositoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Repository\PropositionOwnershipRepositoryInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * Proposition ORM repository
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class PropositionRepository extends EntityRepository implements
    PropositionRepositoryInterface,
    PropositionOwnershipRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findUserProposition(ProductInterface $product, $username)
    {
        return $this->findOneBy(
            [
                'product' => $product,
                'author' => $username,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function findApprovableByUser(User $user, $limit = null)
    {
        $qb = $this->createQueryBuilder('p');

        $qb
            ->join('p.product', 'product')
            ->leftJoin('product.categories', 'category')
            ->innerJoin('PimEnterpriseSecurityBundle:CategoryOwnership', 'o', 'WITH', 'o.category = category')
            ->where(
                $qb->expr()->in('o.role', ':roles')
            )
            ->andWhere(
                $qb->expr()->eq('p.status', Proposition::READY)
            )
            ->orderBy('p.createdAt', 'desc')
            ->setParameter('roles', $user->getRoles());

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
    public function createDatagridQueryBuilder()
    {
        return $this->createQueryBuilder('p');
    }

    /**
     * {@inheritdoc}
     *
     * @param \Doctrine\ORM\QueryBuilder
     */
    public function applyDatagridContext($qb, $productId)
    {
        $qb->innerJoin('p.product', 'product', 'WITH', $qb->expr()->eq('product.id', $productId));

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
