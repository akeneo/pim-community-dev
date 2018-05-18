<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Doctrine\ORM\Repository;

use Akeneo\Component\StorageUtils\Repository\CursorableRepositoryInterface;
use Akeneo\Component\StorageUtils\Repository\SearchableRepositoryInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\DataGridBundle\Doctrine\ORM\Repository\MassActionRepositoryInterface;
use Pim\Component\Catalog\Model\EntityWithValuesInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use PimEnterprise\Component\Workflow\Model\EntityWithValuesDraftInterface;
use PimEnterprise\Component\Workflow\Repository\EntityWithValuesDraftRepositoryInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class EntityWithValuesDraftRepository extends EntityRepository implements EntityWithValuesDraftRepositoryInterface, CursorableRepositoryInterface, SearchableRepositoryInterface, MassActionRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findUserEntityWithValuesDraft(EntityWithValuesInterface $entityWithValues, string $username): ?EntityWithValuesInterface
    {
        return $this->findOneBy(
            [
                'entityWithValues' => $entityWithValues,
                'author' => $username,
            ]
        );
    }

/**
     * {@inheritdoc}
     */
    public function findByEntityWithValues(EntityWithValuesInterface $entityWithValues): ?array
    {
        return $this->findBy(['entityWithValue' => $entityWithValues]);
    }

    /**
     * {@inheritdoc}
     */
    public function findApprovableByUser(UserInterface $user, ?int $limit = null): ?array
    {
        $qb = $this->createApprovableByUserQueryBuilder($user);

        if (null !== $limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function createDatagridQueryBuilder(array $parameters = []): QueryBuilder
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
     */
    public function applyDatagridContext(QueryBuilder $qb, ?string $productId): EntityWithValuesDraftRepositoryInterface
    {
        $qb->innerJoin('p.product', 'product', 'WITH', 'product.id = :product');
        $qb->setParameter('product', $productId);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function applyFilter(QueryBuilder $qb, string $field, string $operator, $value): void
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
            case Operators::IN_ARRAY_KEYS:
                $this->applyFilterInArrayKeys($qb, $field, $value);
                break;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function applySorter(QueryBuilder $qb, string $field, ?string $direction): void
    {
        $fieldName = $this->getRootFieldName($qb, $field);
        $qb->orderBy($fieldName, $direction);
    }

    /**
     * {@inheritdoc}
     */
    public function findByIds(array $productDraftIds): ?array
    {
        $qb = $this->createQueryBuilder('d');
        $qb->where($qb->expr()->in('d.id', ':entity_with_values_ids'));
        $qb->setParameter('entity_with_values_ids', $productDraftIds);

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function applyMassActionParameters($qb, $inset, array $values)
    {
        if (!empty($values)) {
            $condition = $inset ? Operators::IN_LIST : Operators::NOT_IN_LIST;
            $qb->addFilter('id', $condition, $values);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('p')
            ->select('count(p.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getItemsFromIdentifiers(array $identifiers)
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.id IN (:ids)')
            ->setParameter('ids', $identifiers);

        $query = $qb->getQuery();
        $query->useQueryCache(false);

        return $query->execute();
    }

    /**
     * {@inheritdoc}
     *
     * @return UserInterface[]
     */
    public function findBySearch($search = null, array $options = [])
    {
        $qb = $this->createQueryBuilder('p');
        $qb->select('p')->distinct(true);

        if (null !== $search && '' !== $search) {
            $qb->where('p.author like :search')->setParameter('search', '%' . $search . '%');
        }

        if (isset($options['identifiers']) && is_array($options['identifiers']) && !empty($options['identifiers'])) {
            $qb->andWhere('p.author in (:codes)');
            $qb->setParameter('codes', $options['identifiers']);
        }

        if (isset($options['limit'])) {
            $qb->setMaxResults((int) $options['limit']);
            if (isset($options['page'])) {
                $qb->setFirstResult((int) $options['limit'] * ((int) $options['page'] - 1));
            }
        }

        return $qb->getQuery()->getResult();
    }

    /**
     */
    protected function createApprovableByUserQueryBuilder(UserInterface $user): QueryBuilder
    {
        $qb = $this->createQueryBuilder('entity_with_values');

        return $qb
            ->join('entity_with_values.product', 'product')
            ->leftJoin('product.categories', 'category')
            ->innerJoin('PimEnterpriseSecurityBundle:ProductCategoryAccess', 'a', 'WITH', 'a.category = category')
            ->where($qb->expr()->eq('a.ownItems', true))
            ->andWhere($qb->expr()->in('a.userGroup', ':userGroups'))
            ->andWhere($qb->expr()->eq('entity_with_values.status', EntityWithValuesDraftInterface::READY))
            ->orderBy('entity_with_values.createdAt', 'desc')
            ->setParameter('userGroups', $user->getGroups()->toArray())
            ->distinct(true);
    }

    /**
     * Build field name with root alias
     */
    protected function getRootFieldName(QueryBuilder $qb, string $field): string
    {
        if (false === strpos($field, '.')) {
            $field = sprintf("%s.%s", current($qb->getRootAliases()), $field);
        }

        return $field;
    }

    /**
     * Apply an in list filter
     *
     * @param QueryBuilder $qb
     * @param string       $field
     * @param mixed        $value
     */
    protected function applyFilterInList(QueryBuilder $qb, string $field, $value): void
    {
        if (!empty($value)) {
            $qb->andWhere($qb->expr()->in($field, $value));
        }
    }

    /**
     * Apply a between filter
     */
    protected function applyFilterBetween(QueryBuilder $qb, string $field, array $value): void
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
     */
    protected function applyFilterNotBetween(QueryBuilder $qb, string $field, array $value): void
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
     */
    protected function applyFilterGreaterThan(QueryBuilder $qb, string $field, $value): void
    {
        $qb->andWhere($qb->expr()->gt($field, $qb->expr()->literal($this->getDateValue($value, true))));
    }

    /**
     * Apply a lower than filter
     */
    protected function applyFilterLowerThan(QueryBuilder $qb, string $field, $value): void
    {
        $qb->andWhere($qb->expr()->lt($field, $qb->expr()->literal($this->getDateValue($value))));
    }

    /**
     * Apply a in array keys filter
     */
    protected function applyFilterInArrayKeys(QueryBuilder $qb, string $field, $value): void
    {
        $expr = $qb->expr()->orX();
        foreach ($value as $index => $code) {
            $paramKey = sprintf('field_%s', (string) $index);

            // In the ODM version we store that kind of fields in a sub-path
            // named values that need to be removed in ORM
            $field = preg_replace('/\.values$/', '', $field);

            $expr->add($qb->expr()->like($this->getRootFieldName($qb, $field), sprintf(':%s', $paramKey)));
            $qb->setParameter($paramKey, sprintf('%%\"%s\":%%', $code));
        }

        $qb->andWhere($expr);
    }

    /**
     * Get the date formatted from data
     *
     * @param \DateTime|string $data
     * @param bool             $endOfDay
     *
     * @return string
     */
    protected function getDateValue($data, bool $endOfDay = false): string
    {
        if ($data instanceof \DateTime && true === $endOfDay) {
            $data->setTime(23, 59, 59);
        } elseif (!$data instanceof \DateTime && true === $endOfDay) {
            $data = sprintf('%s 23:59:59', $data);
        }

        return $data instanceof \DateTime ? $data->format('Y-m-d H:i:s') : $data;
    }
}
