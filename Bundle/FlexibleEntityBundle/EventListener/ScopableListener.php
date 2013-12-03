<?php

namespace Oro\Bundle\FlexibleEntityBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Oro\Bundle\FlexibleEntityBundle\Model\Behavior\ScopableInterface;
use Oro\Bundle\FlexibleEntityBundle\Model\FlexibleInterface;

/**
 * Aims to inject selected scope into loaded entity
 */
class ScopableListener implements EventSubscriber
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
     * @return ScopableListener
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

        // inject selected scope on scopable containers
        if ($entity instanceof ScopableInterface) {
            // get flexible entity class
            $flexibleEntityClass = ClassUtils::getRealClass(get_class($entity));

            $metadata = $args->getEntityManager()->getClassMetadata($flexibleEntityClass);
            $flexibleConfig = $this->container->getParameter('oro_flexibleentity.flexible_config');
            if ($flexibleEntityClass
                && !$metadata->isMappedSuperclass
                && array_key_exists($flexibleEntityClass, $flexibleConfig['entities_config'])
            ) {
                // get flexible config and manager
                $flexibleManagerName = $flexibleConfig['entities_config'][$flexibleEntityClass]['flexible_manager'];
                $flexibleManager = $this->container->get($flexibleManagerName);
                // set scope setted in manager
                $entity->setScope($flexibleManager->getScope());
            }
        }
    }
}
