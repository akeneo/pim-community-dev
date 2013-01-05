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
     * Related class name
     * @var string
     */
    protected $attributeName;

    /**
     * Related class name
     * @var string
     */
    protected $attributeOptionName;

    /**
     * Related class name
     * @var string
     */
    protected $attributeOptionValueName;

    /**
     * Related class name
     * @var string
     */
    protected $entityValueName;

    /**
     * Default locale code (from config)
     * @var string
     */
    protected $defaultLocaleCode;

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
        $this->flexibleConfig            = $allFlexibleConfig['entities_config'][$entityName];
        $this->attributeName             = $this->flexibleConfig['flexible_attribute_class'];
        $this->attributeOptionName       = $this->flexibleConfig['flexible_attribute_option_class'];
        $this->attributeOptionValueName  = $this->flexibleConfig['flexible_attribute_option_value_class'];
        $this->entityValueName           = $this->flexibleConfig['flexible_entity_value_class'];
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
     * Return locale code from rapplication config
     *
     * @return string
     */
    public function getDefaultLocaleCode()
    {
        if (!$this->defaultLocaleCode) {
            $this->defaultLocaleCode = $this->getLocaleHelper()->getDefaultLocaleCode();
        }

        return $this->defaultLocaleCode;
    }

    /**
     * Set locale code, to force it
     *
     * @param string $code
     *
     * @return FlexibleEntityManager
     */
    public function setDefaultLocaleCode($code)
    {
        $this->defaultLocaleCode = $code;

        return $this;
    }

    /**
     * Return locale code from request or default
     *
     * @return string
     */
    public function getLocaleCode()
    {
        if (!$this->localeCode) {
            $this->localeCode = $this->getLocaleHelper()->getCurrentLocaleCode();
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
        return $this->attributeName;
    }

    /**
     * Return class name that can be used to get the repository or instance
     * @return string
     */
    public function getAttributeOptionName()
    {
        return $this->attributeOptionName;
    }

    /**
     * Return class name that can be used to get the repository or instance
     * @return string
     */
    public function getAttributeOptionValueName()
    {
        return $this->attributeOptionValueName;
    }

    /**
     * Return class name that can be used to get the repository or instance
     * @return string
     */
    public function getEntityValueName()
    {
        return $this->entityValueName;
    }

    /**
     * Return related repository
     * @return Doctrine\Common\Persistence\ObjectRepository
     */
    public function getEntityRepository()
    {
        $repo = $this->storageManager->getRepository($this->entityName);
        $repo->setFlexibleConfig($this->flexibleConfig);

        $repo->setDefaultLocaleCode($this->getDefaultLocaleCode());
        $repo->setLocaleCode($this->getLocaleCode());

        return $repo;
    }

    /**
     * Return related repository
     * @return Doctrine\Common\Persistence\ObjectRepository
     */
    public function getAttributeRepository()
    {
        return $this->storageManager->getRepository($this->attributeName);
    }

    /**
     * Return related repository
     * @return Doctrine\Common\Persistence\ObjectRepository
     */
    public function getAttributeOptionRepository()
    {
        return $this->storageManager->getRepository($this->attributeOptionName);
    }

    /**
     * Return related repository
     * @return Doctrine\Common\Persistence\ObjectRepository
     */
    public function getAttributeOptionValueRepository()
    {
        return $this->storageManager->getRepository($this->attributeOptionValueName);
    }

    /**
     * Return related repository
     * @return Doctrine\Common\Persistence\ObjectRepository
     */
    public function getEntityValueRepository()
    {
        return $this->storageManager->getRepository($this->entityValueName);
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
