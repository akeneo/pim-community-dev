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
     * @var string
     */
    protected $attributeShortname;

    /**
     * @var string
     */
    protected $attributeOptionShortname;

    /**
     * @var string
     */
    protected $attributeValueShortname;

    /**
     * Constructor.
     *
     * @param ObjectManager $om
     * @param string        $entitySN
     * @param string        $attributeSN
     * @param string        $optionSN
     * @param string        $valueSN
     */
    public function __construct(ObjectManager $om, $entitySN, $attributeSN, $optionSN, $valueSN)
    {
        parent::__construct($om, $entitySN);
        $this->attributeShortname = $attributeSN;
        $this->attributeOptionShortname = $optionSN;
        $this->attributeValueShortname = $valueSN;
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
     * Return shortname that can be used to get the repository or instance
     * @return string
     */
    public function getAttributeOptionShortname()
    {
        return $this->attributeOptionShortname;
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

        return new $class();
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
     * @return EntityAttributeValue
     */
    public function getNewAttributeValueInstance()
    {
        $class = $this->getAttributeValueClass();

        return new $class();
    }

    /**
     * Clone an entity
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
