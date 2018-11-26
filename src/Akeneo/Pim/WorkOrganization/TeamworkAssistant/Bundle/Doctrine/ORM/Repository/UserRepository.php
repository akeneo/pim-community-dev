<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Doctrine\ORM\Repository;

use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Repository\UserRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\SearchableRepositoryInterface;
use Akeneo\UserManagement\Component\Model\GroupInterface;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class UserRepository extends EntityRepository implements UserRepositoryInterface, SearchableRepositoryInterface
{
    /**
     * @param EntityManager $em
     * @param ClassMetadata $class
     */
    public function __construct(EntityManager $em, $class)
    {
        parent::__construct($em, $em->getClassMetadata($class));
    }

    /**
     * {@inheritdoc}
     */
    public function findUsersToNotify(ProjectInterface $project)
    {
        $qb = $this->createQueryBuilder('u');

        $groupIdentifiers = $this->extractContributorGroupIdentifier($project);

        if (empty($groupIdentifiers)) {
            return [];
        }

        $qb->leftJoin('u.groups', 'g')
            ->andWhere($qb->expr()->in('g.id', $groupIdentifiers));

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function isProjectContributor(ProjectInterface $project, UserInterface $user)
    {
        $qb = $this->createQueryBuilder('u');

        $groupIdentifiers = $this->extractContributorGroupIdentifier($project);

        if (empty($groupIdentifiers)) {
            return false;
        }

        $qb->distinct(true)
            ->leftJoin('u.groups', 'g')
            ->where($qb->expr()->eq('u.id', $user->getId()))
            ->andWhere($qb->expr()->in('g.id', $groupIdentifiers));

        $contributor = $qb->getQuery()->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);

        return null !== $contributor;
    }

    /**
     * Allow to find contributors that belong to a project.
     *
     * {@inheritdoc}
     */
    public function findBySearch($search = null, array $options = [])
    {
        $searchResolver = $this->configureSearchOptions();
        $options = $searchResolver->resolve($options);

        $qb = $this->createQueryBuilder('u');

        $project = $options['project'];
        $groupIds = array_map(function (GroupInterface $userGroup) {
            return $userGroup->getId();
        }, $project->getUserGroups()->toArray());

        if (empty($groupIds)) {
            return [];
        }

        $qb->leftJoin('u.groups', 'g');
        $qb->andWhere($qb->expr()->in('g.id', $groupIds));

        $qb->setMaxResults($options['limit']);
        $qb->setFirstResult($options['limit'] * ($options['page'] - 1));

        if (null !== $search && '' !== $search) {
            $qb->where('CONCAT(u.firstName, \' \', u.lastName) LIKE :search')
                ->setParameter('search', sprintf('%%%s%%', $search));
        }

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

        $searchResolver->setRequired(['project']);
        $searchResolver->setDefault('limit', 20);
        $searchResolver->setDefault('page', 1);
        $searchResolver->setAllowedTypes('limit', 'numeric');
        $searchResolver->setAllowedTypes('page', 'numeric');
        $searchResolver->setAllowedTypes('project', ProjectInterface::class);

        return $searchResolver;
    }

    /**
     * Extract the contributor group identifiers.
     *
     * @param ProjectInterface $project
     *
     * @return array
     */
    protected function extractContributorGroupIdentifier(ProjectInterface $project)
    {
        $groupIdentifiers = array_map(function (GroupInterface $userGroup) {
            return $userGroup->getId();
        }, $project->getUserGroups()->toArray());

        if (empty($groupIdentifiers)) {
            return [];
        }

        return $groupIdentifiers;
    }
}
