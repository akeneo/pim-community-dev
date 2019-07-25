<?php

namespace Akeneo\UserManagement\Bundle\EventListener;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Tool\Component\Classification\Model\CategoryInterface;
use Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\UnitOfWork;

/**
 * Aims to add/remove locales, channels and trees to user preference choices
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserPreferencesListener
{
    /** @var array */
    protected $metadata = [];

    /** @var array */
    protected $deactivatedLocales = [];

    /** @var CategoryRepositoryInterface */
    private $categoryRepository;

    /** @var ChannelRepositoryInterface */
    private $channelRepository;

    /** @var LocaleRepositoryInterface */
    private $localeRepository;

    /** @var UserRepositoryInterface */
    private $userRepository;

    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->channelRepository = $channelRepository;
        $this->localeRepository = $localeRepository;
        $this->userRepository = $userRepository;
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
     * @param ChannelInterface       $removedChannel
     */
    protected function onChannelRemoved(
        UnitOfWork $uow,
        EntityManagerInterface $manager,
        ChannelInterface $removedChannel
    ) {
        $users = $this->userRepository->findBy(['catalogScope' => $removedChannel]);
        $channels = $this->channelRepository->findAll();

        $defaultScope = current(
            array_filter(
                $channels,
                function ($channel) use ($removedChannel) {
                    return $channel->getCode() !== $removedChannel->getCode();
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
     * @param CategoryInterface $removedTree
     */
    protected function onTreeRemoved(UnitOfWork $uow, EntityManagerInterface $manager, CategoryInterface $removedTree)
    {
        $users = $this->userRepository->findBy(['defaultTree' => $removedTree]);
        $trees = $this->categoryRepository->getTrees();

        $defaultTree = current(
            array_filter(
                $trees,
                function ($tree) use ($removedTree) {
                    return $tree->getCode() !== $removedTree->getCode();
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
        $activeLocales = $this->localeRepository->getActivatedLocales();
        $defaultLocale = current($activeLocales);

        foreach ($this->deactivatedLocales as $localeCode) {
            $deactivatedLocale = $this->localeRepository->findOneByIdentifier($localeCode);
            $users = $this->userRepository->findBy(['catalogLocale' => $deactivatedLocale]);

            foreach ($users as $user) {
                $user->setCatalogLocale($defaultLocale);
                $manager->persist($user);
            }
        }
        $this->deactivatedLocales = [];

        $manager->flush();
    }
}
