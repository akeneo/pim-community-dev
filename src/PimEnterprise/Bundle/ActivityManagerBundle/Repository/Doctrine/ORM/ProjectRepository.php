<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ActivityManager\Bundle\Repository\Doctrine\ORM;

use Akeneo\ActivityManager\Component\Model\ProjectInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Repository\SearchableRepositoryInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\UserBundle\Entity\Group;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class ProjectRepository extends EntityRepository implements
    IdentifiableObjectRepositoryInterface,
    SearchableRepositoryInterface
{
    /**
     * @param EntityManager $em
     * @param string        $class
     */
    public function __construct(EntityManager $em, $class)
    {
        parent::__construct($em, $em->getClassMetadata($class));
    }

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

        $qb = $this->createQueryBuilder('proj');
        if (null !== $search && '' !== $search) {
            $qb->where('proj.label LIKE :search')->setParameter('search', sprintf('%%%s%%', $search));
        }

        $userGroups = $options['user']->getGroups();
        if (!$userGroups->isEmpty()) {
            $userGroupsId = array_map(function (Group $userGroup) {
                return $userGroup->getId();
            }, $userGroups->toArray());

            $qb->innerJoin('proj.userGroups', 'u_groups')
                ->andWhere('u_groups.id IN (:groups)')
                ->setParameter('groups', $userGroupsId);
        }

        $qb->setMaxResults($options['limit']);
        $qb->setFirstResult($options['limit'] * ($options['page'] - 1));

        return $qb->getQuery()->execute();
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
