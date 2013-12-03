<?php

namespace Oro\Bundle\FlexibleEntityBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Oro\Bundle\FlexibleEntityBundle\Model\FlexibleInterface;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeOption;
use Oro\Bundle\FlexibleEntityBundle\Model\Behavior\TranslatableInterface;

/**
 * Aims to inject selected locale into loaded translatable container, ie, not store locale code but contains some
 * translated "children" (values for flexible, option value for option) and allow to select relevant child
 */
class TranslatableListener implements EventSubscriber
{

    /**
     * @var ContainerInterface $container
     */
    protected $container;

    /**
     * Inject service container
     *
     * @param ContainerInterface $container
     *
     * @return TranslatableListener
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
            'postLoad'
        );
    }

    /**
     * After load
     * @param LifecycleEventArgs $args
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        // inject selected locale only for translatable "containers"
        if ($entity instanceof TranslatableInterface) {

            // get flexible entity class
            $flexibleEntityClass = false;
            if ($entity instanceof AbstractAttributeOption) {
                $flexibleEntityClass = $entity->getAttribute()->getEntityType();
            } else {
                $flexibleEntityClass = ClassUtils::getRealClass(get_class($entity));
            }

            $metadata = $args->getEntityManager()->getClassMetadata($flexibleEntityClass);
            $flexibleConfig = $this->container->getParameter('oro_flexibleentity.flexible_config');
            if ($flexibleEntityClass
                && !$metadata->isMappedSuperclass
                && array_key_exists($flexibleEntityClass, $flexibleConfig['entities_config'])
            ) {
                // get flexible config and manager
                $flexibleManagerName = $flexibleConfig['entities_config'][$flexibleEntityClass]['flexible_manager'];
                $flexibleManager = $this->container->get($flexibleManagerName);
                // set locale setted in manager
                $entity->setLocale($flexibleManager->getLocale());
            }
        }
    }
}
