<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Query\Filter\Operators;
use PimEnterprise\Bundle\WorkflowBundle\Doctrine\Repository;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraft;
use PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftRepositoryInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * ProductDraft ORM repository
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class ProductDraftRepository extends EntityRepository implements ProductDraftRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findUserProductDraft(ProductInterface $product, $username)
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
            ->innerJoin('PimEnterpriseSecurityBundle:ProductCategoryAccess', 'a', 'WITH', 'a.category = category')
            ->where(
                $qb->expr()->eq('a.ownItems', true)
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
    public function createProposalDatagridQueryBuilder(array $parameters = [])
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb
            ->select('p, p.createdAt as createdAt, p.changes as changes, p.author as author')
            ->from($this->_entityName, 'p', 'p.id')
            ->join('p.product', 'product')
            ->groupBy('p.id');

        if (isset($parameters['currentUser'])) {
            $user = $parameters['currentUser'];
            if (!is_object($user) || !$user instanceof UserInterface) {
                throw new \InvalidArgumentException(
                    'Current user should be a \Symfony\Component\Security\Core\User\UserInterface.'
                );
            }

            $qb
                ->leftJoin('product.categories', 'category')
                ->innerJoin('PimEnterpriseSecurityBundle:ProductCategoryAccess', 'a', 'WITH', 'a.category = category')
                ->where(
                    $qb->expr()->eq('a.ownItems', true)
                )
                ->andWhere(
                    $qb->expr()->in('a.userGroup', ':userGroups')
                )
                ->andWhere(
                    $qb->expr()->eq('p.status', ProductDraft::READY)
                )
                ->setParameter('userGroups', $user->getGroups()->toArray());
        }

        return $qb;
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
        $field = $this->getRootFieldName($qb, $field);

        switch ($operator) {
            case Operators::IN_LIST:
                $this->applyFilterInList($qb, $field, $value);
                break;
            case Operators::BETWEEN:
                $this->applyFilterBetween($qb, $field, $value);
                break;
            case Operators::NOT_BETWEEN:
                $this->applyFilterNotBetween($qb, $field, $value);
                break;
            case Operators::GREATER_THAN:
                $this->applyFilterGreaterThan($qb, $field, $value);
                break;
            case Operators::LOWER_THAN:
                $this->applyFilterLowerThan($qb, $field, $value);
                break;
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
     * {@inheritdoc}
     */
    public function findByIds(array $ids)
    {
        $qb = $this->createQueryBuilder('d');
        $qb->where($qb->expr()->in('d.id', $ids));

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getDistinctAuthors()
    {
        $alias = 'p';
        $authorField = $alias.'.author';
        $queryBuilder = $this->_em->createQueryBuilder()
            ->select($authorField)
            ->from($this->_entityName, $alias, $authorField)
            ->distinct(true)
            ->orderBy($authorField);

        $authors = $queryBuilder->getQuery()->getArrayResult();
        $authorCodes = array_keys($authors);
        ksort($authorCodes);

        return $authorCodes;
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

    /**
     * Apply an in list filter
     *
     * @param QueryBuilder $qb
     * @param string       $field
     * @param mixed        $value
     */
    protected function applyFilterInList(QueryBuilder $qb, $field, $value)
    {
        if (!empty($value)) {
            $qb->andWhere($qb->expr()->in($field, $value));
        }
    }

    /**
     * Apply a between filter
     *
     * @param QueryBuilder $qb
     * @param string       $field
     * @param array        $value
     */
    protected function applyFilterBetween(QueryBuilder $qb, $field, array $value)
    {
        $qb->andWhere(
            $qb->expr()->andX(
                $qb->expr()->gt($field, $qb->expr()->literal($this->getDateValue($value[0]))),
                $qb->expr()->lt($field, $qb->expr()->literal($this->getDateValue($value[1], true)))
            )
        );
    }

    /**
     * Apply a not between filter
     *
     * @param QueryBuilder $qb
     * @param string       $field
     * @param array        $value
     */
    protected function applyFilterNotBetween(QueryBuilder $qb, $field, array $value)
    {
        $qb->andWhere(
            $qb->expr()->orX(
                $qb->expr()->lt($field, $qb->expr()->literal($this->getDateValue($value[0]))),
                $qb->expr()->gt($field, $qb->expr()->literal($this->getDateValue($value[1], true)))
            )
        );
    }

    /**
     * Apply a greater than filter
     *
     * @param QueryBuilder $qb
     * @param string       $field
     * @param mixed        $value
     */
    protected function applyFilterGreaterThan(QueryBuilder $qb, $field, $value)
    {
        $qb->andWhere($qb->expr()->gt($field, $qb->expr()->literal($this->getDateValue($value, true))));
    }

    /**
     * Apply a lower than filter
     *
     * @param QueryBuilder $qb
     * @param string       $field
     * @param mixed        $value
     */
    protected function applyFilterLowerThan(QueryBuilder $qb, $field, $value)
    {
        $qb->andWhere($qb->expr()->lt($field, $qb->expr()->literal($this->getDateValue($value))));
    }

    /**
     * Get the date formatted from data
     *
     * @param \DateTime|string $data
     * @param bool             $endOfDay
     *
     * @return string
     */
    protected function getDateValue($data, $endOfDay = false)
    {
        if ($data instanceof \DateTime && true === $endOfDay) {
            $data->setTime(23, 59, 59);
        } elseif (!$data instanceof \DateTime && true === $endOfDay) {
            $data = sprintf('%s 23:59:59', $data);
        }

        return $data instanceof \DateTime ? $data->format('Y-m-d H:i:s') : $data;
    }
}
