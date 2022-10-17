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

use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Repository\ProjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\UserManagement\Component\Model\GroupInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class ProjectRepository extends EntityRepository implements ProjectRepositoryInterface
{
    protected ?CursorFactoryInterface $cursorFactory;

    /**
     * @retrun string[]
     */
    public function getIdentifierProperties(): array
    {
        return ['code'];
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($identifier): ?ProjectInterface
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

        $qb->leftJoin('project.userGroups', 'u_groups');
        $qb->join('project.owner', 'owner');

        $userGroups = $options['user']->getGroups();
        if (!$userGroups->isEmpty()) {
            $userGroupsId = array_map(
                function (GroupInterface $userGroup) {
                    return $userGroup->getId();
                },
                $userGroups->toArray()
            );

            $qb->orWhere($qb->expr()->in('u_groups.id', ':groups'));
            $qb->setParameter(':groups', $userGroupsId);
        }

        $qb->orWhere($qb->expr()->eq('owner.username', ':username'));
        $qb->setParameter('username', $options['user']->getUserIdentifier());

        if (null !== $search && '' !== $search) {
            $qb->andWhere('project.label LIKE :search')->setParameter('search', sprintf('%%%s%%', $search));
        }

        $qb->setMaxResults($options['limit']);
        $qb->setFirstResult($options['limit'] * ($options['page'] - 1));
        $qb->orderBy('project.dueDate, project.id');

        return $qb->getQuery()->execute();
    }

    /**
     * Returns a cursor with all products
     *
     * @throws \RuntimeException If cursor has not been set
     */
    public function findAll(): CursorInterface
    {
        if (null === $this->cursorFactory) {
            throw new \LogicException('The cursor factory is not initialized');
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

    public function findByLocale(LocaleInterface $locale): CursorInterface
    {
        if (null === $this->cursorFactory) {
            throw new \LogicException('The cursor factory is not initialized');
        }

        $qb = $this->createQueryBuilder('project');
        $qb->where($qb->expr()->eq('project.locale', $locale->getId()));

        return $this->cursorFactory->createCursor($qb);
    }

    public function findByChannel(ChannelInterface $channel): CursorInterface
    {
        if (null === $this->cursorFactory) {
            throw new \LogicException('The cursor factory is not initialized');
        }

        $qb = $this->createQueryBuilder('project');
        $qb->where($qb->expr()->eq('project.channel', $channel->getId()));

        return $this->cursorFactory->createCursor($qb);
    }

    public function setCursorFactory(CursorFactoryInterface $cursorFactory)
    {
        $this->cursorFactory = $cursorFactory;
    }

    /**
     * Initialize, configure and returns an options resolver for findBySearch query.
     */
    protected function configureSearchOptions(): OptionsResolver
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
