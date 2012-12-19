<?php
namespace Oro\Bundle\DataModelBundle\Entity;

use Oro\Bundle\DataModelBundle\Model\Entity as AbstractEntity;
use Oro\Bundle\DataModelBundle\Model\EntityAttributeValue as AbstractEntityAttributeValue;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Base Doctrine ORM entity
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT
 *
 */
abstract class Entity extends AbstractEntity
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
     * @var datetime $created
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @var datetime $updated
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    protected $updated;

    /**
     * @var Value
     *
     * @ORM\OneToMany(targetEntity="EntityAttributeValue", mappedBy="entity", cascade={"persist", "remove"})
     */
    protected $values;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->values = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add value
     *
     * @param AbstractEntityAttributeValue $value
     *
     * @return AbstractEntity
     */
    public function addValue(AbstractEntityAttributeValue $value)
    {
        $this->values[] = $value;
        $value->setEntity($this);

        return $this;
    }

    /**
     * Define "magic" getter / setter to set values
     *
     * @param string $name
     * @param array $arguments
     */
    public function __call($method, $arguments)
    {
        $command = substr($method, 0, 3);
        $attCode = lcfirst(substr($method, 3));
/*        if ($command == "set") {
            $this->set($field, $args);
        } else*/ if ($command == "get") {

            $values = $this->getValues()->filter(function($value) use ($attCode) {

                return $value->getAttribute()->getCode() == $attCode;
            });
            $value = $values->first();
            return ($value) ? $value->getData() : null;
        } else {

            // twig pass here !

            $values = $this->getValues()->filter(function($value) use ($method) {
                return $value->getAttribute()->getCode() == $method;
            });
            $value = $values->first();
            return ($value) ? $value->getData() : null;

//            echo $method;

            return null;
//            throw new \BadMethodCallException("There is no method ".$method);
        }
    }

}
