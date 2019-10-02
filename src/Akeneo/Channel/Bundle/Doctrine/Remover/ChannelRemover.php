<?php

namespace Akeneo\Channel\Bundle\Doctrine\Remover;

use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * ChannelRemover used as service to remove given channel
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelRemover implements RemoverInterface
{
    /** @var ObjectManager */
    protected $objectManager;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var string */
    protected $entityClass;

    /**
     * @param ObjectManager               $objectManager
     * @param EventDispatcherInterface    $eventDispatcher
     * @param ChannelRepositoryInterface  $channelRepository
     * @param TranslatorInterface         $translator
     * @param string                      $entityClass
     */
    public function __construct(
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher,
        ChannelRepositoryInterface $channelRepository,
        TranslatorInterface $translator,
        $entityClass
    ) {
        $this->objectManager = $objectManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->channelRepository = $channelRepository;
        $this->translator = $translator;
        $this->entityClass = $entityClass;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($object, array $options = [])
    {
        $this->validateObject($object);

        $objectId = $object->getId();

        $options['unitary'] = true;

        $this->eventDispatcher->dispatch(StorageEvents::PRE_REMOVE, new RemoveEvent($object, $objectId, $options));

        $this->objectManager->remove($object);
        $this->objectManager->flush();

        $this->eventDispatcher->dispatch(StorageEvents::POST_REMOVE, new RemoveEvent($object, $objectId, $options));
    }

    /**
     * @param $object
     *
     * @throws \LogicException
     */
    private function validateObject($object)
    {
        if (!$object instanceof $this->entityClass) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "%s", "%s" provided.',
                    $this->entityClass,
                    ClassUtils::getClass($object)
                )
            );
        }

        $channelCount = $this->channelRepository->countAll();
        if (1 === $channelCount) {
            throw new \LogicException($this->translator->trans(
                'pim_enrich.channel.flash.delete.error',
                ['%channelCode%' => $object->getCode() ]
            ));
        }
    }
}
