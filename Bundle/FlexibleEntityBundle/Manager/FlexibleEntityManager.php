<?php
namespace Oro\Bundle\FlexibleEntityBundle\Manager;

use Oro\Bundle\FlexibleEntityBundle\Model\Entity;
use Oro\Bundle\FlexibleEntityBundle\Model\EntityAttribute;
use Oro\Bundle\FlexibleEntityBundle\Model\EntityAttributeValue;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Flexible object manager, allow to use flexible entity in storage agnostic way
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class FlexibleEntityManager extends SimpleEntityManager
{

    /**
     * Flexible entity config
     * @var array
     */
    protected $flexibleConfig;

    /**
     * Locale code (from request or choose by user)
     * @
     * @var string
     */
    protected $localeCode;

    /**
     * Constructor
     *
     * @param ContainerInterface $container      service container
     * @param string             $entityName     entity name
     */
    public function __construct($container, $entityName)
    {
        parent::__construct($container, $entityName);
        // get flexible entity configuration
        $allFlexibleConfig = $this->container->getParameter('oro_flexibleentity.entities_config');
        $this->flexibleConfig = $allFlexibleConfig['entities_config'][$entityName];
    }

    /**
     * Get flexible entity config
     * @return array
     */
    public function getFlexibleConfig()
    {
        return $this->flexibleConfig;
    }

    /**
     * Get locale helper
     * @return LocaleHelper
     */
    public function getLocaleHelper()
    {
        return $this->container->get('oro_flexibleentity.locale_helper');
    }

    /**
     * Is translatable flexible entity
     *
     * @return boolean
     */
    public function isTranslatableEntity()
    {
        return $this->flexibleConfig['has_translatable_value'];
    }

    /**
     * Is scopable flexible entity
     *
     * @return boolean
     */
    public function isScopableEntity()
    {
        return $this->flexibleConfig['has_scopable_value'];
    }

    /**
     * Return locale code from request or default
     *
     * @return string
     */
    public function getLocaleCode()
    {
        if (!$this->localeCode) {
            // get current locale by default if translatable
            if ($this->isTranslatableEntity()) {
                $this->localeCode = $this->getLocaleHelper()->getCurrentLocaleCode();
                // if not get application default locale
            } else {
                $this->localeCode = $this->getLocaleHelper()->getDefaultLocaleCode();
            }
        }

        return $this->localeCode;
    }

    /**
     * Set locale code, to force it
     *
     * @param string $code
     *
     * @return FlexibleEntityManager
     */
    public function setLocaleCode($code)
    {
        $this->localeCode = $code;

        return $this;
    }

    /**
     * Return class name that can be used to get the repository or instance
     * @return string
     */
    public function getAttributeName()
    {
        return $this->flexibleConfig['flexible_attribute_class'];
    }

    /**
     * Return class name that can be used to get the repository or instance
     * @return string
     */
    public function getAttributeOptionName()
    {
        return $this->flexibleConfig['flexible_attribute_option_class'];
    }

    /**
     * Return class name that can be used to get the repository or instance
     * @return string
     */
    public function getAttributeOptionValueName()
    {
        return $this->flexibleConfig['flexible_attribute_option_value_class'];
    }

    /**
     * Return class name that can be used to get the repository or instance
     * @return string
     */
    public function getEntityValueName()
    {
        return $this->flexibleConfig['flexible_entity_value_class'];
    }

    /**
     * Return related repository
     * @return Doctrine\Common\Persistence\ObjectRepository
     */
    public function getEntityRepository()
    {
        $repo = $this->storageManager->getRepository($this->getEntityName());
        $repo->setFlexibleConfig($this->flexibleConfig);
        $repo->setLocaleCode($this->getLocaleCode());

        return $repo;
    }

    /**
     * Return related repository
     * @return Doctrine\Common\Persistence\ObjectRepository
     */
    public function getAttributeRepository()
    {
        return $this->storageManager->getRepository($this->getAttributeName());
    }

    /**
     * Return related repository
     * @return Doctrine\Common\Persistence\ObjectRepository
     */
    public function getAttributeOptionRepository()
    {
        return $this->storageManager->getRepository($this->getAttributeOptionName());
    }

    /**
     * Return related repository
     * @return Doctrine\Common\Persistence\ObjectRepository
     */
    public function getAttributeOptionValueRepository()
    {
        return $this->storageManager->getRepository($this->getAttributeOptionValueName());
    }

    /**
     * Return related repository
     * @return Doctrine\Common\Persistence\ObjectRepository
     */
    public function getEntityValueRepository()
    {
        return $this->storageManager->getRepository($this->getEntityValueName());
    }

    /**
     * Return a new instance
     * @return Entity
     */
    public function createEntity()
    {
        $class = $this->getEntityName();

        return new $class();
    }

    /**
     * Return a new instance
     * @return EntityAttribute
     */
    public function createAttribute()
    {
        $class = $this->getAttributeName();
        $object = new $class();
        $object->setEntityType($this->getEntityName());

        return $object;
    }

    /**
     * Return a new instance
     * @return EntityAttributeOption
     */
    public function createNewAttributeOption()
    {
        $class = $this->getAttributeOptionName();

        return new $class();
    }

    /**
     * Return a new instance
     * @return EntityAttributeOption
     */
    public function createAttributeOptionValue()
    {
        $class = $this->getAttributeOptionValueName();
        $object = new $class();
        $object->setLocaleCode($this->getLocaleCode());

        return $object;
    }

    /**
     * Return a new instance
     * @return EntityAttributeValue
     */
    public function createEntityValue()
    {
        $class = $this->getEntityValueName();
        $object = new $class();
        $object->setLocaleCode($this->getLocaleCode());

        return $object;
    }

}
