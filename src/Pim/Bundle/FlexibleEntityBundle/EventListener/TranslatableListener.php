<?php

namespace Pim\Bundle\FlexibleEntityBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttributeOption;
use Pim\Bundle\FlexibleEntityBundle\Model\Behavior\TranslatableInterface;

/**
 * Aims to inject selected locale into loaded translatable container, ie, not store locale code but contains some
 * translated "children" (values for flexible, option value for option) and allow to select relevant child
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        return [
            'postLoad'
        ];
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
            $flexibleConfig = $this->container->getParameter('pim_flexibleentity.flexible_config');
            if ($flexibleEntityClass &&
                !$metadata->isMappedSuperclass &&
                array_key_exists($flexibleEntityClass, $flexibleConfig['entities_config'])) {

                // get flexible config and manager
                $flexibleManagerName = $flexibleConfig['entities_config'][$flexibleEntityClass]['flexible_manager'];
                $flexibleManager = $this->container->get($flexibleManagerName);
                // set locale setted in manager
                $entity->setLocale($flexibleManager->getLocale());
            }
        }
    }
}
