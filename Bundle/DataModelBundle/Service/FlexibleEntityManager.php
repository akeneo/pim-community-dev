<?php
namespace Oro\Bundle\DataModelBundle\Service;

use Oro\Bundle\DataModelBundle\Model\Entity;
use Oro\Bundle\DataModelBundle\Model\EntityAttribute;
use Oro\Bundle\DataModelBundle\Model\EntityAttributeValue;
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
     * Locale code
     * @var string
     */
    protected $defaultLocaleCode;

    /**
     * Locale code
     * @var string
     */
    protected $localeCode;

    /**
     * Default value
     * @var string
     */
    protected $attributeShortname = 'OroDataModelBundle:OrmEntityAttribute';

    /**
     * Default value
     * @var string
     */
    protected $attributeOptionShortname = 'OroDataModelBundle:OrmEntityAttributeOption';

    /**
     * Default value
     * @var string
     */
    protected $attributeOptionValueShortname = 'OroDataModelBundle:OrmEntityAttributeOptionValue';

    /**
     * @var string
     */
    protected $attributeValueShortname;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container service container
     * @param string             $entitySN  entity short name
     * @param string             $valueSN   value short name
     */
    public function __construct($container, $entitySN, $valueSN)
    {
        parent::__construct($container, $entitySN);
        $this->attributeValueShortname = $valueSN;
    }

    /**
     * Return locale code from rapplication config
     * TODO: custom config ?
     *
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
     * Return locale code from request or default
     * TODO: custom config ?
     *
     * @return string
     */
    public function getLocaleCode()
    {
        if (!$this->localeCode) {
            $this->localeCode = $this->container->initialized('request') ?
                $this->container->get('request')->getLocale() : false;
            if (!$this->localeCode) {
                $this->localeCode = $this->getDefaultLocaleCode();
            }
        }

        return $this->localeCode;
    }

    /**
     * Return related repository
     * @return Doctrine\Common\Persistence\ObjectRepository
     */
    public function getEntityRepository()
    {
        $repo = $this->manager->getRepository($this->getEntityShortname());
        $repo->setDefaultLocaleCode($this->getDefaultLocaleCode());
        $repo->setLocaleCode($this->getLocaleCode());

        return $repo;
    }

    /**
     * Return shortname that can be used to get the repository or instance
     * @return string
     */
    public function getAttributeShortname()
    {
        return $this->attributeShortname;
    }

    /**
     * Set shortname that can be used to get the repository or instance
     *
     * @param string $attributeSN
     *
     * @return FlexibleEntityManager
     */
    public function setAttributeShortname($attributeSN)
    {
        $this->attributeShortname = $attributeSN;

        return $this;
    }

    /**
     * Return shortname that can be used to get the repository or instance
     * @return string
     */
    public function getAttributeOptionShortname()
    {
        return $this->attributeOptionShortname;
    }

    /**
     * Set shortname that can be used to get the repository or instance
     *
     * @param string $attributeOptionSN
     *
     * @return FlexibleEntityManager
     */
    public function setAttributeOptionShortname($attributeOptionSN)
    {
        $this->attributeOptionShortname = $attributeOptionSN;

        return $this;
    }

    /**
     * Return shortname that can be used to get the repository or instance
     * @return string
     */
    public function getAttributeOptionValueShortname()
    {
        return $this->attributeOptionValueShortname;
    }

    /**
     * Set shortname that can be used to get the repository or instance
     *
     * @param string $attributeOptionValueSN
     *
     * @return FlexibleEntityManager
     */
    public function setAttributeOptionValueShortname($attributeOptionValueSN)
    {
        $this->attributeOptionValueShortname = $attributeOptionValueSN;

        return $this;
    }

    /**
     * Return shortname that can be used to get the repository or instance
     * @return string
     */
    public function getAttributeValueShortname()
    {
        return $this->attributeValueShortname;
    }

    /**
     * Return implementation class that can be use to instanciate
     * @return string
     */
    public function getAttributeClass()
    {
        return $this->manager->getClassMetadata($this->getAttributeShortname())->getName();
    }

    /**
     * Return implementation class that can be use to instanciate
     * @return string
     */
    public function getAttributeOptionClass()
    {
        return $this->manager->getClassMetadata($this->getAttributeOptionShortname())->getName();
    }

    /**
     * Return implementation class that can be use to instanciate
     * @return string
     */
    public function getAttributeOptionValueClass()
    {
        return $this->manager->getClassMetadata($this->getAttributeOptionValueShortname())->getName();
    }

    /**
     * Return implementation class that can be use to instanciate
     * @return string
     */
    public function getAttributeValueClass()
    {
        return $this->manager->getClassMetadata($this->getAttributeValueShortname())->getName();
    }

    /**
     * Return related repository
     * @return Doctrine\Common\Persistence\ObjectRepository
     */
    public function getAttributeRepository()
    {
        return $this->manager->getRepository($this->getAttributeShortname());
    }

    /**
     * Return related repository
     * @return Doctrine\Common\Persistence\ObjectRepository
     */
    public function getAttributeOptionRepository()
    {
        return $this->manager->getRepository($this->getAttributeOptionShortname());
    }

    /**
     * Return related repository
     * @return Doctrine\Common\Persistence\ObjectRepository
     */
    public function getAttributeOptionValueRepository()
    {
        return $this->manager->getRepository($this->getAttributeOptionValueShortname());
    }

    /**
     * Return related repository
     * @return Doctrine\Common\Persistence\ObjectRepository
     */
    public function getAttributeValueRepository()
    {
        return $this->manager->getRepository($this->getAttributeValueShortname());
    }

    /**
     * Return a new instance
     * @return Entity
     */
    public function getNewEntityInstance()
    {
        $class = $this->getEntityClass();

        return new $class();
    }

    /**
     * Return a new instance
     * @return EntityAttribute
     */
    public function getNewAttributeInstance()
    {
        $class = $this->getAttributeClass();
        $object = new $class();
        $object->setEntityType($this->getEntityShortname());

        return $object;
    }

    /**
     * Return a new instance
     * @return EntityAttributeOption
     */
    public function getNewAttributeOptionInstance()
    {
        $class = $this->getAttributeOptionClass();

        return new $class();
    }

    /**
     * Return a new instance
     * @return EntityAttributeOption
     */
    public function getNewAttributeOptionValueInstance()
    {
        $class = $this->getAttributeOptionValueClass();
        $object = new $class();
        $object->setLocaleCode($this->getLocaleCode());

        return $object;
    }

    /**
     * Return a new instance
     * @return EntityAttributeValue
     */
    public function getNewAttributeValueInstance()
    {
        $class = $this->getAttributeValueClass();
        $object = new $class();
        $object->setLocaleCode($this->getLocaleCode());

        return $object;
    }

    /**
     * Clone an entity
     *
     * TODO: see copy() in entitymanager
     *
     * @param Entity $entity to clone
     *
     * @return Entity
     */
    public function cloneEntity($entity)
    {
        // create a new entity
        $class = $this->getEntityClass();
        $clone = new $class();

        // clone values
        foreach ($entity->getValues() as $value) {
            $cloneValue = $this->getNewAttributeValueInstance();
            $cloneValue->setAttribute($value->getAttribute());
            $cloneValue->setData($value->getData());
            $clone->addValue($cloneValue);
        }

        return $clone;
    }
}
