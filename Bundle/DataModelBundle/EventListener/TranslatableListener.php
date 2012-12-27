<?php
namespace Oro\Bundle\DataModelBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Oro\Bundle\DataModelBundle\Entity\AbstractOrmEntity;

/**
 * Aims to add translatable behaviour on flexible entity
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class TranslatableListener implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * After load a flexible entity
     * @param LifecycleEventArgs $args
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();

        // TODO : should be enhanced for perfs (cache) ?

        // you only want to act on some "AbstractOrmEntity" entity to add locale and default locale codes
        if (is_subclass_of($entity, 'Oro\Bundle\DataModelBundle\Entity\AbstractOrmEntity')) {

            // get default and selected locale
            $defaultLocaleCode = $this->container->parameters['locale'];
            $localeCode = $this->container->initialized('request') ? $this->container->get('request')->getLocale() : false;
            if (!$localeCode) {
                $localeCode = $defaultLocaleCode;
            }

            // setup entity
            $entity->setDefaultLocaleCode($defaultLocaleCode);
            $entity->setLocaleCode($localeCode);
        }
    }
}