<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Common\Saver;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\Saver\SavingOptionsResolverInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Event\ChannelEvents;
use Pim\Bundle\CatalogBundle\Manager\CompletenessManager;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Channel saver, contains custom logic for channel saving
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelSaver implements SaverInterface
{
    /** @var ObjectManager */
    protected $objectManager;

    /** @var CompletenessManager */
    protected $completenessManager;

    /** @var SavingOptionsResolverInterface */
    protected $optionsResolver;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /**
     * @param ObjectManager                  $objectManager
     * @param CompletenessManager            $completenessManager
     * @param SavingOptionsResolverInterface $optionsResolver
     * @param EventDispatcherInterface       $eventDispatcher
     */
    public function __construct(
        ObjectManager $objectManager,
        CompletenessManager $completenessManager,
        SavingOptionsResolverInterface $optionsResolver,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->objectManager       = $objectManager;
        $this->completenessManager = $completenessManager;
        $this->optionsResolver     = $optionsResolver;
        $this->eventDispatcher     = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function save($channel, array $options = [])
    {
        if (!$channel instanceof ChannelInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "Pim\Bundle\CatalogBundle\Model\ChannelInterface", "%s" provided.',
                    get_class($channel)
                )
            );
        }

        $this->eventDispatcher->dispatch(ChannelEvents::PRE_SAVE, new GenericEvent($channel));

        $options = $this->optionsResolver->resolveSaveOptions($options);
        $this->objectManager->persist($channel);
        if (true === $options['schedule']) {
            $this->completenessManager->scheduleForChannel($channel);
        }
        if (true === $options['flush']) {
            $this->objectManager->flush();
        }

        $this->eventDispatcher->dispatch(ChannelEvents::POST_SAVE, new GenericEvent($channel));
    }
}
