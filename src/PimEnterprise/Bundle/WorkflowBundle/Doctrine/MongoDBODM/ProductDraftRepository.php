<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Doctrine\MongoDBODM;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Doctrine\ORM\AbstractQuery;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraft;
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
    protected $categoryAccessRepository;

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
        if (null === $this->categoryAccessRepository) {
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
     * @return \Doctrine\ODM\MongoDB\Query\Builder
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
     * @param \Doctrine\ODM\MongoDB\Query\Builder $qb
     */
    public function applyDatagridContext($qb, $productId)
    {
        $qb->field('product.$id')->equals(new \MongoId($productId));

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param \Doctrine\ODM\MongoDB\Query\Builder $qb
     */
    public function applyFilter($qb, $field, $operator, $value)
    {
        if ('IN' === $operator) {
            if (!empty($value)) {
                $qb->field($field)->in($value);
            }
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param \Doctrine\ODM\MongoDB\Query\Builder $qb
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
        $this->categoryAccessRepository = $repository;

        return $this;
    }

    /**
     * Creates a QB with proposals that are approvable by the user
     *
     * @param UserInterface $user
     *
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    protected function createApprovableQueryBuilder(UserInterface $user)
    {
        $qb = $this->createQueryBuilder('ProductDraft');

        $qb
            ->field('status')->equals(ProductDraft::READY)
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
        $qb = $this->categoryAccessRepository->createQueryBuilder('o');

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
}
