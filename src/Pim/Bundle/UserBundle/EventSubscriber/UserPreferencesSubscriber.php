<?php

namespace Pim\Bundle\UserBundle\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Pim\Component\Catalog\Model\CategoryInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Aims to add/remove locales, channels and trees to user preference choices
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserPreferencesSubscriber implements EventSubscriber
{
    /** @var ContainerInterface */
    protected $container;

    /** @var array */
    protected $metadata = [];

    /** @var array */
    protected $deactivatedLocales = [];

    /**
     * Inject service container
     *
     * @param ContainerInterface $container
     *
     * @return UserPreferencesSubscriber
     */
    public function setContainer($container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Specifies the list of events to listen
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            'onFlush',
            'postFlush',
        ];
    }

    /**
     * On flush
     *
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $manager = $args->getEntityManager();
        $uow = $manager->getUnitOfWork();
        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            $this->preUpdate($uow, $entity);
        }
        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            $this->preRemove($uow, $manager, $entity);
        }
    }

    /**
     * Post flush
     *
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        $manager = $args->getEntityManager();

        if (!empty($this->deactivatedLocales)) {
            $this->onLocalesDeactivated($manager);
        }
    }

    /**
     * Before remove
     *
     * @param UnitOfWork             $uow
     * @param EntityManagerInterface $manager
     * @param object                 $entity
     */
    protected function preRemove(UnitOfWork $uow, EntityManagerInterface $manager, $entity)
    {
        if ($entity instanceof ChannelInterface) {
            $this->onChannelRemoved($uow, $manager, $entity);
        }

        if ($entity instanceof CategoryInterface && $entity->isRoot()) {
            $this->onTreeRemoved($uow, $manager, $entity);
        }
    }

    /**
     * Before update
     *
     * @param object $entity
     */
    protected function preUpdate(UnitOfWork $uow, $entity)
    {
        if ($entity instanceof LocaleInterface && !$entity->isActivated()) {
            $changeset = $uow->getEntityChangeSet($entity);
            if (isset($changeset['activated'])) {
                $this->deactivatedLocales[] = $entity->getCode();
            }
        }
    }

    /**
     * Get the metadata of an entity
     *
     * @param EntityManagerInterface $manager
     * @param object                 $entity
     *
     * @return array
     */
    protected function getMetadata(EntityManagerInterface $manager, $entity)
    {
        $className = get_class($entity);
        if (!isset($this->metadata[$className])) {
            $this->metadata[$className] = $manager->getClassMetadata($className);
        }

        return $this->metadata[$className];
    }

    /**
     * Compute changeset
     *
     * @param UnitOfWork             $uow
     * @param EntityManagerInterface $manager
     * @param object                 $entity
     */
    protected function computeChangeset(UnitOfWork $uow, EntityManagerInterface $manager, $entity)
    {
        $uow->persist($entity);
        $uow->computeChangeSet($this->getMetadata($manager, $entity), $entity);
    }

    /**
     * Update catalog scope of users using a channel that will be removed
     *
     * @param UnitOfWork             $uow
     * @param EntityManagerInterface $manager
     * @param ChannelInterface       $channel
     */
    protected function onChannelRemoved(
        UnitOfWork $uow,
        EntityManagerInterface $manager,
        ChannelInterface $channel
    ) {
        $users = $this->findUsersBy(['catalogScope' => $channel]);
        $scopes = $this->container->get('pim_catalog.repository.channel')->findAll();

        $defaultScope = current(
            array_filter(
                $scopes,
                function ($scope) use ($channel) {
                    return $scope->getCode() !== $channel->getCode();
                }
            )
        );

        foreach ($users as $user) {
            $user->setCatalogScope($defaultScope);
            $this->computeChangeset($uow, $manager, $user);
        }
    }

    /**
     * Update default tree of users using a tree that will be removed
     *
     * @param CategoryInterface $category
     */
    protected function onTreeRemoved(UnitOfWork $uow, EntityManagerInterface $manager, CategoryInterface $category)
    {
        $users = $this->findUsersBy(['defaultTree' => $category]);
        $trees = $this->container->get('pim_catalog.repository.category')->getTrees();

        $defaultTree = current(
            array_filter(
                $trees,
                function ($tree) use ($category) {
                    return $tree->getCode() !== $category->getCode();
                }
            )
        );

        foreach ($users as $user) {
            $user->setDefaultTree($defaultTree);
            $this->computeChangeset($uow, $manager, $user);
        }
    }

    /**
     * Update catalog locale of users using a deactivated locale
     */
    protected function onLocalesDeactivated(EntityManagerInterface $manager)
    {
        $localeRepository = $this->container->get('pim_catalog.repository.locale');
        $activeLocales = $localeRepository->getActivatedLocales();
        $defaultLocale = current($activeLocales);

        foreach ($this->deactivatedLocales as $localeCode) {
            $deactivatedLocale = $localeRepository->findOneByIdentifier($localeCode);
            $users = $this->findUsersBy(['catalogLocale' => $deactivatedLocale]);

            foreach ($users as $user) {
                $user->setCatalogLocale($defaultLocale);
                $manager->persist($user);
            }
        }
        $this->deactivatedLocales = [];

        $manager->flush();
    }

    /**
     * Return users matching the specified criteria
     *
     * @param array $criteria
     *
     * @return array
     */
    protected function findUsersBy(array $criteria)
    {
        return $this->container->get('oro_user.manager')->getRepository()->findBy($criteria);
    }
}
