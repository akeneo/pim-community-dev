<?php

namespace Akeneo\Tool\Bundle\VersioningBundle\EventSubscriber;

use Akeneo\Tool\Bundle\VersioningBundle\Factory\VersionFactory;
use Akeneo\Tool\Bundle\VersioningBundle\Repository\VersionRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Akeneo\Tool\Component\Versioning\Model\VersionableInterface;
use Doctrine\Common\Util\ClassUtils;
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
        $this->versionFactory = $versionFactory;
        $this->versionRepository = $versionRepository;
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
        $this->versionSaver = $versionSaver;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::PRE_REMOVE => 'addRemoveVersion',
        ];
    }

    /**
     * @param RemoveEvent $event
     */
    public function addRemoveVersion(RemoveEvent $event)
    {
        $author = '';
        $subject = $event->getSubject();

        if (!$subject instanceof VersionableInterface) {
            return;
        }

        if (null !== ($token = $this->tokenStorage->getToken()) &&
            $this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')
        ) {
            $author = $token->getUser()->getUsername();
        }

        $previousVersion = $this->versionRepository->getNewestLogEntry(
            ClassUtils::getClass($subject),
            $event->getSubjectId()
        );

        $version = $this->versionFactory->create(
            ClassUtils::getClass($subject),
            $event->getSubjectId(),
            $author,
            'Deleted'
        );

        $version->setVersion(null !== $previousVersion ? $previousVersion->getVersion() + 1 : 1)
            ->setSnapshot(null !== $previousVersion ? $previousVersion->getSnapshot(): [])
            ->setChangeset([]);

        $options = $event->getArguments();
        $this->versionSaver->save($version, $options);
    }
}
