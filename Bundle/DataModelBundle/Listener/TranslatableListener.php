<?php
namespace Oro\Bundle\DataModelBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\Common\EventSubscriber;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Aims to add translatable behaviour on flexible entity
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class TranslatableListener implements EventSubscriber, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

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
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Get locale helper
     * @return LocaleHelper
     */
    public function getLocaleHelper()
    {
        return $this->container->get('oro_datamodel.locale_helper');
    }

    /**
     * After load
     * @param LifecycleEventArgs $args
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();
        // add locale and default locale codes on translatable containers
        if ($entity instanceof \Oro\Bundle\DataModelBundle\Model\Behavior\TranslatableContainerInterface) {
            $entity->setDefaultLocaleCode($this->getLocaleHelper()->getDefaultLocaleCode());
            $entity->setLocaleCode($this->getLocaleHelper()->getCurrentLocaleCode());
        }
    }

}