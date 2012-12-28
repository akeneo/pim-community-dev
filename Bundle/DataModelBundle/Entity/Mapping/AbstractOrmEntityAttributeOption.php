<?php
namespace Oro\Bundle\DataModelBundle\Entity\Mapping;

use Oro\Bundle\DataModelBundle\Model\AbstractEntityAttributeOption;
use Oro\Bundle\DataModelBundle\Model\AbstractEntityAttributeOptionValue;
use Doctrine\ORM\Mapping as ORM;

/**
 * Base Doctrine ORM entity attribute option
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT  MIT
 *
 */
abstract class AbstractOrmEntityAttributeOption extends AbstractEntityAttributeOption
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
     * @var Attribute $attribute
     *
     * @ORM\ManyToOne(targetEntity="AbstractOrmEntityAttribute")
     * @ORM\JoinColumn(name="attribute_id", nullable=false, onDelete="CASCADE", referencedColumnName="id")
     */
    protected $attribute;

    /**
     * @ORM\Column(name="sort_order", type="integer")
     */
    protected $sortOrder;

    /**
     * @var ArrayCollection $optionValues
     *
     * @ORM\OneToMany(targetEntity="AbstractOrmEntityAttributeOptionValue", mappedBy="option", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $optionValues;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->optionValues    = new \Doctrine\Common\Collections\ArrayCollection();
        $this->sortOrder = 1;
    }

    /**
     * Set attribute
     *
     * @param AbstractOrmEntityAttribute $attribute
     *
     * @return EntityAttributeOption
     */
    public function setAttribute(AbstractOrmEntityAttribute $attribute = null)
    {
        $this->attribute = $attribute;

        return $this;
    }

    /**
     * Add option value
     *
     * @param AbstractEntityAttributeOptionValue $value
     *
     * @return AbstractEntityAttribute
     */
    public function addOptionValue(AbstractEntityAttributeOptionValue $value)
    {
        $this->optionValues[] = $value;
        $value->setOption($this);

        return $this;
    }
}
