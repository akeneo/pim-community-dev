<?php

namespace Pim\Bundle\VersioningBundle\EventSubscriber;

use Akeneo\Bundle\StorageUtilsBundle\Event\BaseEvents;
use Akeneo\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Util\ClassUtils;
use Pim\Bundle\CatalogBundle\Event\AssociationTypeEvents;
use Pim\Bundle\CatalogBundle\Event\AttributeEvents;
use Pim\Bundle\CatalogBundle\Event\CategoryEvents;
use Pim\Bundle\CatalogBundle\Event\FamilyEvents;
use Pim\Bundle\CatalogBundle\Event\GroupEvents;
use Pim\Bundle\CatalogBundle\Event\ProductEvents;
use Pim\Bundle\VersioningBundle\Factory\VersionFactory;
use Pim\Bundle\VersioningBundle\Repository\VersionRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Add current user
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddRemoveVersionSubscriber implements EventSubscriberInterface
{
    /** @var VersionFactory */
    protected $versionFactory;

    /** @var VersionRepositoryInterface */
    protected $versionRepository;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var SaverInterface */
    protected $versionSaver;

    /**
     * @param VersionFactory                $versionFactory
     * @param VersionRepositoryInterface    $versionRepository
     * @param TokenStorageInterface         $tokenStorage
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param SaverInterface                $versionSaver
     */
    public function __construct(
        VersionFactory $versionFactory,
        VersionRepositoryInterface $versionRepository,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        SaverInterface $versionSaver
    ) {
        $this->versionFactory       = $versionFactory;
        $this->versionRepository    = $versionRepository;
        $this->tokenStorage         = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
        $this->versionSaver         = $versionSaver;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            AssociationTypeEvents::POST_REMOVE   => 'postRemove',
            AttributeEvents::POST_REMOVE         => 'postRemove',
            CategoryEvents::POST_REMOVE_CATEGORY => 'postRemove',
            CategoryEvents::POST_REMOVE_TREE     => 'postRemove',
            FamilyEvents::POST_REMOVE            => 'postRemove',
            GroupEvents::POST_REMOVE             => 'postRemove',
            ProductEvents::POST_REMOVE           => 'postRemove',
            BaseEvents::POST_REMOVE              => 'postRemove',
        ];
    }

    /**
     * @param RemoveEvent $event
     */
    public function postRemove(RemoveEvent $event)
    {
        if (null !== ($token = $this->tokenStorage->getToken()) &&
            $this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')
        ) {
            $author = $token->getUser()->getUsername();
        } else {
            $author = '';
        }

        $previousVersion = $this->versionRepository->getNewestLogEntry(
            ClassUtils::getClass($event->getSubject()),
            $event->getSubjectId()
        );

        $version = $this->versionFactory->create(
            ClassUtils::getClass($event->getSubject()),
            $event->getSubjectId(),
            $author,
            'Deleted'
        );
        $version->setVersion(null !== $previousVersion ? $previousVersion->getVersion() + 1 : 1)
            ->setSnapshot(null !== $previousVersion ? $previousVersion->getSnapshot(): [])
            ->setChangeset([]);

        $this->versionSaver->save($version);
    }
}
