<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ActivityManagerBundle\Doctrine\ORM\Repository;

use Akeneo\Bundle\StorageUtilsBundle\Doctrine\ORM\Repository\CursorableRepositoryInterface;
use Akeneo\Component\StorageUtils\Cursor\CursorFactoryInterface;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\UserBundle\Entity\Group;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use PimEnterprise\Component\ActivityManager\Repository\ProjectRepositoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class ProjectRepository extends EntityRepository implements ProjectRepositoryInterface, CursorableRepositoryInterface
{
    /** @var CursorFactoryInterface */
    protected $cursorFactory;

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return ['code'];
    }

    /**
     * {@inheritdoc}
     *
     * @return ProjectInterface
     */
    public function findOneByIdentifier($identifier)
    {
        return $this->findOneBy(['code' => $identifier]);
    }

    /**
     * Allow to find projects by search on label, on projects that a user can access and paginate results.
     *
     * {@inheritdoc}
     *
     * @return ProjectInterface[]
     */
    public function findBySearch($search = null, array $options = null)
    {
        $searchResolver = $this->configureSearchOptions();
        $options = $searchResolver->resolve($options);

        $qb = $this->createQueryBuilder('project');
        $qb->distinct(true);
        if (null !== $search && '' !== $search) {
            $qb->where('project.label LIKE :search')->setParameter('search', sprintf('%%%s%%', $search));
        }

        $qb->leftJoin('project.userGroups', 'u_groups');
        $qb->join('project.owner', 'owner');

        $userGroups = $options['user']->getGroups();
        if (!$userGroups->isEmpty()) {
            $userGroupsId = array_map(
                function (Group $userGroup) {
                    return $userGroup->getId();
                },
                $userGroups->toArray()
            );

            $qb->orWhere($qb->expr()->in('u_groups.id', ':groups'));
            $qb->setParameter(':groups', $userGroupsId);
        }

        $qb->orWhere($qb->expr()->eq('owner.username', ':username'));
        $qb->setParameter('username', $options['user']->getUsername());

        $qb->setMaxResults($options['limit']);
        $qb->setFirstResult($options['limit'] * ($options['page'] - 1));
        $qb->orderBy('project.dueDate');

        return $qb->getQuery()->execute();
    }

    /**
     * TODO: manage transaction/error during the project calculation
     *
     * {@inheritdoc}
     */
    public function addProduct(ProjectInterface $project, ProductInterface $product)
    {
        $this->_em->getConnection()->insert('pimee_activity_manager_project_product', [
            'project_id' => $project->getId(),
            'product_id' => $product->getId(),
        ]);
    }

    /**
     * Returns a cursor with all products
     *
     * @throws \RuntimeException If cursor has not been set
     *
     * @return CursorInterface
     */
    public function findAll()
    {
        if (null === $this->cursorFactory) {
            throw new \RuntimeException('The cursor factory is not initialized');
        }

        $qb = $this->createQueryBuilder('project');

        return $this->cursorFactory->createCursor($qb);
    }

    /**
     * {@inheritdoc}
     */
    public function findByIds(array $projectIds)
    {
        if (empty($projectIds)) {
            throw new \InvalidArgumentException('Array must contain at least one project id');
        }

        $qb = $this->createQueryBuilder('project');
        $qb->where($qb->expr()->in('project.id', ':project_ids'));
        $qb->setParameter(':project_ids', $projectIds);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param CursorFactoryInterface $cursorFactory
     */
    public function setCursorFactory(CursorFactoryInterface $cursorFactory)
    {
        $this->cursorFactory = $cursorFactory;
    }

    /**
     * Initialize, configure and returns an options resolver for findBySearch query.
     *
     * @return OptionsResolver
     */
    protected function configureSearchOptions()
    {
        $searchResolver = new OptionsResolver();

        $searchResolver->setRequired(['user']);
        $searchResolver->setDefault('limit', 20);
        $searchResolver->setDefault('page', 1);
        $searchResolver->setAllowedTypes('limit', 'numeric');
        $searchResolver->setAllowedTypes('page', 'numeric');
        $searchResolver->setAllowedTypes('user', UserInterface::class);

        return $searchResolver;
    }
}
