<?php

namespace Akeneo\Channel\Infrastructure\Doctrine\Remover;

use Akeneo\Channel\Infrastructure\Component\Query\IsChannelUsedInProductExportJobInterface;
use Akeneo\Channel\Infrastructure\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * ChannelRemover used as service to remove given channel
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelRemover implements RemoverInterface
{
    protected ObjectManager $objectManager;
    protected EventDispatcherInterface $eventDispatcher;
    protected ChannelRepositoryInterface $channelRepository;
    protected TranslatorInterface $translator;
    protected string $entityClass;

    private IsChannelUsedInProductExportJobInterface $isChannelUsedInProductExportJob;

    public function __construct(
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher,
        ChannelRepositoryInterface $channelRepository,
        TranslatorInterface $translator,
        IsChannelUsedInProductExportJobInterface $isChannelUsedInProductExportJob,
        $entityClass
    ) {
        $this->objectManager = $objectManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->channelRepository = $channelRepository;
        $this->translator = $translator;
        $this->entityClass = $entityClass;
        $this->isChannelUsedInProductExportJob = $isChannelUsedInProductExportJob;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($object, array $options = [])
    {
        $this->validateObject($object);

        $objectId = $object->getId();

        $options['unitary'] = true;

        $this->eventDispatcher->dispatch(new RemoveEvent($object, $objectId, $options), StorageEvents::PRE_REMOVE);

        $this->objectManager->remove($object);
        $this->objectManager->flush();

        $this->eventDispatcher->dispatch(new RemoveEvent($object, $objectId, $options), StorageEvents::POST_REMOVE);
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

        if (true === $this->isChannelUsedInProductExportJob->execute($object->getCode())) {
            throw new \LogicException($this->translator->trans(
                'pim_enrich.channel.flash.delete.linked_to_export_profile'
            ));
        }
    }
}
