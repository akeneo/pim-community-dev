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
     * Default locale code
     * @var string
     */
    protected $defaultLocaleCode;

    /**
     * Current locale code
     * @var string
     */
    protected $localeCode;

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

        // add locale and default locale codes on "AbstractOrmEntity"
        if (is_subclass_of($entity, 'Oro\Bundle\DataModelBundle\Entity\AbstractOrmEntity')) {
            $entity->setDefaultLocaleCode($this->getDefaultLocaleCode());
            $entity->setLocaleCode($this->getLocaleCode());

        // add locale on "AbstractEntityAttributeOption"
        } else if (is_subclass_of($entity, 'Oro\Bundle\DataModelBundle\Entity\AbstractOrmEntityAttributeOption')) {
            $entity->setLocaleCode($this->getLocaleCode());
        }
    }

    /**
     * TODO : should be refactored, ~ use in repository too
     * @return string
     */
    public function getDefaultLocaleCode()
    {
        if (!$this->defaultLocaleCode) {
            $this->defaultLocaleCode = $this->container->parameters['locale'];
        }

        return $this->defaultLocaleCode;
    }

    /**
     * TODO : should be refactored, ~ use in repository too
     * @return string
     */
    public function getLocaleCode()
    {
        if (!$this->localeCode) {
            $this->localeCode = $this->container->initialized('request') ? $this->container->get('request')->getLocale() : false;
            if (!$this->localeCode) {
                $this->localeCode = $this->getDefaultLocaleCode();
            }
        }

        return $this->localeCode;
    }
}