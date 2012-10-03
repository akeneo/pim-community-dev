<?php

namespace Strixos\CatalogEavBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Bap\FlexibleEntityBundle\Model\Entity;

/**
 * @author     Romain Monceau @ Strixos
 * @copyright  Copyright (c) 2012 Strixos SAS (http://www.strixos.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="StrixosCatalogEav_Product_Entity")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Strixos\CatalogEavBundle\Repository\ProductRepository")
 */
class Product extends Entity
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var EntityType $type
     *
     * @ORM\ManyToOne(targetEntity="Type")
     */
    protected $type;

    /**
     * TODO : test in other way :
     * - store an associative array data field_code -> value
     * - add pre-persist event to save value in relevant table (int, varchare, etc) base on field type
     *
     * @var Value
     *
     * @ORM\OneToMany(targetEntity="Value", mappedBy="product", cascade={"persist", "remove"})
     */
    protected $values;

    /**
     * Add magic getter / setter here
     * TODO: take a look on EntityRepository::__call which define findBy
     * TODO: deal with camel case
     * TODO: enhance perfs
     *
     * @param string $name
     * @param array $arguments
     * @throws \Exception
     */
    public function __call($name, $arguments)
    {
        switch (substr($name, 0, 3)) {
            case 'get':

                $fieldCode = strtolower(substr($name, 3));
                foreach ($this->getType()->getFields() as $field) {
                    if ($field->getCode() == $fieldCode) {
                        foreach ($this->getValues() as $value) {
                            if ($value->getField()->getCode() == $field->getCode()) {
                                return $value->getContent();
                            }
                        }
                    }
                }
                throw new \Exception('exception get field '. $fieldCode .' which not exist !');

            case 'set':

                $fieldCode = strtolower(substr($name, 3));

                foreach ($this->getType()->getFields() as $field) {

                    if ($field->getCode() == $fieldCode) {

                        foreach ($this->getValues() as $value) {
                            if ($value->getField()->getCode() == $field->getCode()) {
                                return $value->setContent($arguments[0]);
                            }
                        }

                        // add value
                        $value = new Value();
                        $value->setField($field);
                        $value->setContent($arguments[0]);
                        // TODO why we have to define in both direction ?
                        // -> perhaps because we don't use persist on manager for value cases ?
                        $this->addValue($value);
                        $value->setProduct($this);

                        return $value->getContent();
                    }
                }

                throw new \Exception('field '. $fieldCode .' not exist !');
        }
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set type
     *
     * @param Strixos\CatalogEavBundle\Entity\Type $type
     * @return Product
     */
    public function setType(\Strixos\CatalogEavBundle\Entity\Type $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return Strixos\CatalogEavBundle\Entity\Type
     */
    public function getType()
    {
        return $this->type;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->values = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add values
     *
     * @param Strixos\CatalogEavBundle\Entity\Value $values
     * @return Product
     */
    public function addValue(\Strixos\CatalogEavBundle\Entity\Value $values)
    {
        $this->values[] = $values;

        return $this;
    }

    /**
     * Remove values
     *
     * @param Strixos\CatalogEavBundle\Entity\Value $values
     */
    public function removeValue(\Strixos\CatalogEavBundle\Entity\Value $values)
    {
        $this->values->removeElement($values);
    }

    /**
     * Get values
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getValues()
    {
        return $this->values;
    }
}