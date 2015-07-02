<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Doctrine\MongoDBODM\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\ORM\AbstractQuery;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Query\Filter\Operators;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface;
use PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftRepositoryInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * ProductDraft ODM repository
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class ProductDraftRepository extends DocumentRepository implements ProductDraftRepositoryInterface
{
    /** @var CategoryAccessRepository */
    protected $categoryAccessRepo;

    /**
     * {@inheritdoc}
     */
    public function findUserProductDraft(ProductInterface $product, $username)
    {
        return $this
            ->createQueryBuilder('ProductDraft')
            ->field('author')->equals($username)
            ->field('product')->references($product)
            ->getQuery()->getSingleResult();
    }

    /**
     * {@inheritdoc}
     */
    public function createProposalDatagridQueryBuilder(array $parameters = [])
    {
        if (null === $this->categoryAccessRepo) {
            throw new \LogicException('Category access repository should be set.');
        }

        if (isset($parameters['currentUser'])) {
            $user = $parameters['currentUser'];
            if (!is_object($user) || !$user instanceof UserInterface) {
                throw new \InvalidArgumentException(
                    'Current user should be a \Symfony\Component\Security\Core\User\UserInterface.'
                );
            }

            $qb = $this->createApprovableQueryBuilder($user);
        } else {
            $qb = $this->createQueryBuilder('ProductDraft');
        }

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function findApprovableByUser(UserInterface $user, $limit = null)
    {
        $qb = $this->createApprovableQueryBuilder($user)
            ->sort('createdAt', 'desc');

        if (null !== $limit) {
            $qb->limit($limit);
        }

        return $qb->getQuery()->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function findByProduct(ProductInterface $product)
    {
        return $this
            ->createQueryBuilder('ProductDraft')
            ->field('product')->references($product)
            ->getQuery()->execute();
    }

    /**
     * {@inheritdoc}
     *
     * @return Builder
     */
    public function createDatagridQueryBuilder(array $parameters = [])
    {
        $qb = $this->createQueryBuilder('p');

        if (isset($parameters['product'])) {
            $this->applyDatagridContext($qb, $parameters['product']);
        }

        return $qb;
    }

    /**
     * {@inheritdoc}
     *
     * @param Builder $qb
     */
    public function applyDatagridContext($qb, $productId)
    {
        $qb->field('product.$id')->equals(new \MongoId($productId));

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param Builder $qb
     */
    public function applyFilter($qb, $field, $operator, $value)
    {
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
     * @param Builder $qb
     */
    public function applySorter($qb, $field, $direction)
    {
        $qb->sort($field, $direction);
    }

    /**
     * @param CategoryAccessRepository $repository
     *
     * @return ProductDraftRepositoryInterface
     */
    public function setCategoryAccessRepository(CategoryAccessRepository $repository)
    {
        $this->categoryAccessRepo = $repository;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function findByIds(array $ids)
    {
        return $this
            ->createQueryBuilder('ProductDraft')
            ->field('_id')->in($ids)
            ->getQuery()->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function getDistinctAuthors()
    {
        $qb = $this->createQueryBuilder('ProductDraft');

        $authors = $qb
            ->distinct('author')
            ->getQuery()
            ->execute();

        $authorCodes = [];
        if ($authors->count() > 0) {
            $authorCodes = $authors->toArray();
            ksort($authorCodes);
        }

        return $authorCodes;
    }

    /**
     * Creates a QB with proposals that are approvable by the user
     *
     * @param UserInterface $user
     *
     * @return Builder
     */
    protected function createApprovableQueryBuilder(UserInterface $user)
    {
        $qb = $this->createQueryBuilder('ProductDraft');

        $qb
            ->field('status')->equals(ProductDraftInterface::READY)
            ->field('categoryIds')->in($this->getGrantedCategoryIds($user));

        return $qb;
    }

    /**
     * Get ids of categories the given user has ownership rights to
     *
     * @param UserInterface $user
     *
     * @return integer[]
     */
    protected function getGrantedCategoryIds(UserInterface $user)
    {
        $qb = $this->categoryAccessRepo->createQueryBuilder('o');

        $qb
            ->join('o.category', 'category')
            ->select('category.id')
            ->where($qb->expr()->in('o.userGroup', ':userGroups'))
            ->andWhere('o.ownProducts = 1')
            ->setParameter('userGroups', $user->getGroups()->toArray());

        $result = $qb->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY);

        $grantedCategoryIds = [];
        foreach ($result as $row) {
            $grantedCategoryIds[] = $row['id'];
        }

        return $grantedCategoryIds;
    }

    /**
     * Apply an in list filter
     *
     * @param Builder $qb
     * @param string  $field
     * @param mixed   $value
     */
    protected function applyFilterInList(Builder $qb, $field, $value)
    {
        if (!empty($value)) {
            $qb->field($field)->in($value);
        }
    }

    /**
     * Apply a between filter
     *
     * @param Builder $qb
     * @param string  $field
     * @param array   $value
     */
    protected function applyFilterBetween(Builder $qb, $field, array $value)
    {
        $qb->field($field)->gte($this->getDateValue($value[0]));
        $qb->field($field)->lte($this->getDateValue($value[1], true));
    }

    /**
     * Apply a not between filter
     *
     * @param Builder $qb
     * @param string  $field
     * @param array   $value
     */
    protected function applyFilterNotBetween(Builder $qb, $field, array $value)
    {
        $qb->addAnd(
            $qb->expr()
                ->addOr($qb->expr()->field($field)->lte($this->getDateValue($value[0])))
                ->addOr($qb->expr()->field($field)->gte($this->getDateValue($value[1], true)))
        );
    }

    /**
     * Apply a greater than filter
     *
     * @param Builder $qb
     * @param string  $field
     * @param mixed   $value
     */
    protected function applyFilterGreaterThan(Builder $qb, $field, $value)
    {
        $qb->field($field)->gt($this->getDateValue($value, true));
    }

    /**
     * Apply a lower than filter
     *
     * @param Builder $qb
     * @param string  $field
     * @param mixed   $value
     */
    protected function applyFilterLowerThan(Builder $qb, $field, $value)
    {
        $qb->field($field)->lt($this->getDateValue($value));
    }

    /**
     * Get the date formatted from data
     *
     * @param \DateTime|string $data
     * @param bool             $endOfDay
     *
     * @return \DateTime
     */
    protected function getDateValue($data, $endOfDay = false)
    {
        if ($data instanceof \DateTime && true === $endOfDay) {
            $data->setTime(23, 59, 59);
        } elseif (!$data instanceof \DateTime && true === $endOfDay) {
            $data = sprintf('%s 23:59:59', $data);
        }

        return $data instanceof \DateTime ? $data : new \DateTime($data);
    }
}
