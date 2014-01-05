<?php

namespace Pim\Bundle\FlexibleEntityBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Pim\Bundle\FlexibleEntityBundle\Model\Behavior\ScopableInterface;

/**
 * Aims to inject selected scope into loaded entity
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
            $flexibleConfig = $this->container->getParameter('pim_flexibleentity.flexible_config');
            if ($flexibleEntityClass &&
                !$metadata->isMappedSuperclass &&
                array_key_exists($flexibleEntityClass, $flexibleConfig['entities_config'])) {

                // get flexible config and manager
                $flexibleManagerName = $flexibleConfig['entities_config'][$flexibleEntityClass];
                $flexibleManager = $this->container->get($flexibleManagerName);

                // set scope setted in manager
                $scopeCode = $flexibleManager->getScope();
                $entity->setScope($scopeCode);
            }
        }
    }
}
