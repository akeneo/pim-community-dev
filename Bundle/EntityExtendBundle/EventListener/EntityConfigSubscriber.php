<?php

namespace Oro\Bundle\FlexibleBundle\EventListener;

use Metadata\MetadataFactory;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Oro\Bundle\EntityConfigBundle\Event\EntityConfigEvent;
use Oro\Bundle\EntityConfigBundle\Event\Events;
use Oro\Bundle\EntityConfigBundle\Config\ValueConfig;

use Oro\Bundle\FlexibleBundle\Config\FlexibleConfigManager;

class EntityConfigSubscriber implements EventSubscriberInterface
{
    /**
     * @var FlexibleConfigManager
     */
    protected $fmm;

    /**
     * @var MetadataFactory
     */
    protected $metadataFactory;

    /**
     * @param FlexibleConfigManager     $flexibleMetaManager
     * @param \Metadata\MetadataFactory $metadataFactory
     */
    public function __construct(FlexibleConfigManager $flexibleMetaManager, MetadataFactory $metadataFactory)
    {
        $this->fmm             = $flexibleMetaManager;
        $this->metadataFactory = $metadataFactory;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            Events::prePersistEntityConfig => 'prePersist'
        );
    }

    /**
     * @param EntityConfigEvent $event
     */
    public function prePersist(EntityConfigEvent $event)
    {
        if ($this->metadataFactory->getMetadataForClass($event->getEntityConfig()->getClassName())) {
            /**
             * TODO:: make it with a FlexibleConfigManager
             */
            $configIsExtend = new ValueConfig();
            $configIsExtend->setScope($this->fmm->getScope());
            $configIsExtend->setCode('is_extend');
            $configIsExtend->setValue(true);

            $configClass = new ValueConfig();
            $configClass->setScope($this->fmm->getScope());
            $configClass->setCode('class');
            $configClass->setValue($this->fmm->generateExtendClassName($event->getEntityConfig()->getClassName()));

            $event->getEntityConfig()->addValue($configClass);
            $event->getEntityConfig()->addValue($configIsExtend);
        }
    }
}
