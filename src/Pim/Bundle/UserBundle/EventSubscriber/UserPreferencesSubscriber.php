<?php

namespace Pim\Bundle\UserBundle\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;

/**
 * Aims to add/remove locales, channels and trees to user preference choices
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserPreferencesSubscriber implements EventSubscriber
{
    /**
     * @var ContainerInterface $container
     */
    protected $container;

    /**
     * @var EntityManager $manager
     */
    protected $manager;

    /**
     * @var UnitOfWork $uow
     */
    protected $uow;

    /**
     * @var array
     */
    private $metadata = array();

    /**
     * @var array
     */
    private $deactivatedLocales = array();

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
        return array(
            'onFlush',
            'postFlush',
        );
    }

    /**
     * On flush
     *
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $this->manager = $args->getEntityManager();
        $this->uow = $this->manager->getUnitOfWork();
        foreach ($this->uow->getScheduledEntityUpdates() as $entity) {
            $this->preUpdate($entity);
        }
        foreach ($this->uow->getScheduledEntityDeletions() as $entity) {
            $this->preRemove($entity);
        }
    }

    /**
     * Post flush
     *
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        $this->manager = $args->getEntityManager();

        if (!empty($this->deactivatedLocales)) {
            $this->onLocalesDeactivated();
        }
    }

    /**
     * Before remove
     *
     * @param object $entity
     */
    protected function preRemove($entity)
    {
        if ($entity instanceof ChannelInterface) {
            $this->onChannelRemoved($entity);
        }

        if ($entity instanceof CategoryInterface && $entity->isRoot()) {
            $this->onTreeRemoved($entity);
        }
    }

    /**
     * Before update
     *
     * @param object $entity
     */
    protected function preUpdate($entity)
    {
        if ($entity instanceof LocaleInterface && !$entity->isActivated()) {
            $changeset = $this->uow->getEntityChangeSet($entity);
            if (isset($changeset['activated'])) {
                $this->deactivatedLocales[] = $entity->getCode();
            }
        }
    }

    /**
     * Get the metadata of an entity
     * @param object $entity
     *
     * @return array
     */
    protected function getMetadata($entity)
    {
        $className = get_class($entity);
        if (!isset($this->metadata[$className])) {
            $this->metadata[$className] = $this->manager->getClassMetadata($className);
        }

        return $this->metadata[$className];
    }

    /**
     * Compute changeset
     * @param object $entity
     */
    protected function computeChangeset($entity)
    {
        $this->uow->persist($entity);
        $this->uow->computeChangeSet($this->getMetadata($entity), $entity);
    }

    /**
     * Update catalog scope of users using a channel that will be removed
     *
     * @param ChannelInterface $channel
     *
     * @return null
     */
    protected function onChannelRemoved(ChannelInterface $channel)
    {
        $users  = $this->findUsersBy(array('catalogScope' => $channel));
        $scopes = $this->container->get('pim_catalog.manager.channel')->getChannels();

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
            $this->computeChangeset($user);
        }
    }

    /**
     * Update default tree of users using a tree that will be removed
     *
     * @param CategoryInterface $category
     *
     * @return null
     */
    protected function onTreeRemoved(CategoryInterface $category)
    {
        $users = $this->findUsersBy(array('defaultTree' => $category));
        $trees = $this->container->get('pim_catalog.manager.category')->getTrees();

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
            $this->computeChangeset($user);
        }
    }

    /**
     * Update catalog locale of users using a deactivated locale
     */
    protected function onLocalesDeactivated()
    {
        $localeManager = $this->container->get('pim_catalog.manager.locale');
        $activeLocales = $localeManager->getActiveLocales();
        $defaultLocale = current($activeLocales);

        foreach ($this->deactivatedLocales as $localeCode) {
            $deactivatedLocale = $localeManager->getLocaleByCode($localeCode);
            $users = $this->findUsersBy(array('catalogLocale' => $deactivatedLocale));

            foreach ($users as $user) {
                $user->setCatalogLocale($defaultLocale);
                $this->manager->persist($user);
            }
        }
        $this->deactivatedLocales = array();

        $this->manager->flush();
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
