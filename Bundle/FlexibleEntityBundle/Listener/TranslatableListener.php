<?php
namespace Oro\Bundle\FlexibleEntityBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\Common\EventSubscriber;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Oro\Bundle\FlexibleEntityBundle\Model\Behavior\TranslatableContainerInterface;
use Oro\Bundle\FlexibleEntityBundle\Model\Behavior\FlexibleEntityInterface;

/**
 * Aims to inject selected locale into loaded entity
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
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
     * @return HasRequiredValueListener
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

        // inject selected locale on translatable containers
        if ($entity instanceof TranslatableContainerInterface) {

            // get flexible entity class
            $flexibleEntityClass = false;
            if ($entity instanceof FlexibleEntityInterface) {
                $flexibleEntityClass = get_class($entity);
            } else if ($entity instanceof \Oro\Bundle\FlexibleEntityBundle\Entity\OrmAttributeOption) {
                $flexibleEntityClass = $entity->getAttribute()->getEntityType();
            }

            if ($flexibleEntityClass) {
                // get flexible config and manager
                $flexibleConfig = $this->container->getParameter('oro_flexibleentity.entities_config');
                $flexibleManagerName = $flexibleConfig['entities_config'][$flexibleEntityClass]['flexible_manager'];
                $flexibleManager = $this->container->get($flexibleManagerName);
                // set locale setted in manager (if not defined, retrieved from http or config)
                $entity->setLocaleCode($flexibleManager->getLocaleCode());
            }
        }
    }

}