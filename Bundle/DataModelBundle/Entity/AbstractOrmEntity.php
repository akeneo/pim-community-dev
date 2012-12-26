<?php
namespace Oro\Bundle\DataModelBundle\Entity;

use Oro\Bundle\DataModelBundle\Model\AbstractEntity;
use Oro\Bundle\DataModelBundle\Model\AbstractEntityAttributeValue;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Base Doctrine ORM entity
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
abstract class AbstractOrmEntity extends AbstractEntity
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
     * @ORM\OneToMany(targetEntity="AbstractOrmEntityAttributeValue", mappedBy="entity", cascade={"persist", "remove"})
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
     * Get value data by attribute code
     *
     * @param string $attCode
     *
     * @return boolean|NULL
     */
    public function __get($attCode)
    {
        // TODO : refactor

        // TODO : getDataText(), getData()

        $values = $this->getValues()->filter(function($value) use ($attCode) {
            return $value->getAttribute()->getCode() == $attCode;
        });
        $value = $values->first();

        return ($value) ? $value->getData() : null;
    }


    /**
     * Define "magic" getter / setter to set values
     *
     * @param string $method    called method
     * @param array  $arguments arguments
     *
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        // TODO : refactor

        return $this->__get($method);
    }


}
