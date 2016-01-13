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
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Bundle\CatalogBundle\Query\Filter\Operators;
use PimEnterprise\Bundle\WorkflowBundle\Doctrine\Repository;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface;
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
    public function findByProductExcludingAuthor(ProductInterface $product, UserInterface $user)
    {
        return $this->createQueryBuilder('ProductDraft')
            ->where('ProductDraft.product = :product')
            ->andWhere('ProductDraft.author <> :author')
            ->setParameter('product', $product)
            ->setParameter('author', $user->getUsername())
            ->getQuery()
            ->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findApprovableByUser(UserInterface $user, $limit = null)
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
    public function findApprovableByUserAndProductId(UserInterface $user, $productId = null, $limit = null)
    {
        $qb = $this->createApprovableByUserQueryBuilder($user);

        if (null !== $productId) {
            $qb->andWhere('product.id = :productId')->setParameter('productId', $productId);
        }

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
                    $qb->expr()->eq('p.status', ProductDraftInterface::READY)
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
            case Operators::IN_ARRAY_KEYS:
                $this->applyFilterInArrayKeys($qb, $field, $value);
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
     * {@inheritdoc}
     */
    public function applyMassActionParameters($qb, $inset, array $values)
    {
        if ($values) {
            $rootAlias = $qb->getRootAlias();
            $valueWhereCondition =
                $inset
                    ? $qb->expr()->in($rootAlias, $values)
                    : $qb->expr()->notIn($rootAlias, $values);
            $qb->andWhere($valueWhereCondition);
        }

        if (null !== $qb->getDQLPart('where')) {
            $whereParts = $qb->getDQLPart('where')->getParts();
            $qb->resetDQLPart('where');

            foreach ($whereParts as $part) {
                if (!is_string($part) || !strpos($part, 'entityIds')) {
                    $qb->andWhere($part);
                }
            }
        }

        $qb->setParameters(
            $qb->getParameters()->filter(
                function ($parameter) {
                    return $parameter->getName() !== 'entityIds';
                }
            )
        );

        $qb->resetDQLPart('orderBy');

        // remove limit of the query
        $qb->setMaxResults(null);
    }

    /**
     * @param UserInterface $user
     *
     * @return QueryBuilder
     */
    protected function createApprovableByUserQueryBuilder(UserInterface $user)
    {
        $qb = $this->createQueryBuilder('product_draft');

        return $qb
            ->join('product_draft.product', 'product')
            ->leftJoin('product.categories', 'category')
            ->innerJoin('PimEnterpriseSecurityBundle:ProductCategoryAccess', 'a', 'WITH', 'a.category = category')
            ->where($qb->expr()->eq('a.ownItems', true))
            ->andWhere($qb->expr()->in('a.userGroup', ':userGroups'))
            ->andWhere($qb->expr()->eq('product_draft.status', ProductDraftInterface::READY))
            ->orderBy('product_draft.createdAt', 'desc')
            ->setParameter('userGroups', $user->getGroups()->toArray())
            ->groupBy('product_draft.id');
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
     * Apply a in array keys filter
     *
     * @param QueryBuilder $qb
     * @param string       $field
     * @param mixed        $value
     */
    protected function applyFilterInArrayKeys(QueryBuilder $qb, $field, $value)
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
