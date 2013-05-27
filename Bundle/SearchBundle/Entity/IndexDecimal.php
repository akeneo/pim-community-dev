<?php

namespace Oro\Bundle\SearchBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Decimal entity for search index
 *
 * @ORM\Table(name="oro_search_index_decimal")
 * @ORM\Entity
 */
class IndexDecimal
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Item", inversedBy="decimalFields")
     * @ORM\JoinColumn(name="item_id", referencedColumnName="id", nullable=false)
     */
    private $item;

    /**
     * @var string
     *
     * @ORM\Column(name="field", type="string", length=250, nullable=false)
     */
    private $field;

    /**
     * @var float
     *
     * @ORM\Column(name="value", type="decimal", scale=2, nullable=false))
     */
    private $value;

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
     * Set item
     *
     * @param  Item         $item
     * @return IndexDecimal
     */
    public function setItem(Item $item = null)
    {
        $this->item = $item;

        return $this;
    }

    /**
     * Get item
     *
     * @return Item
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Set field name
     *
     * @param  string       $field
     * @return IndexDecimal
     */
    public function setField($field)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * Get field name
     *
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Set field value
     *
     * @param  float        $value
     * @return IndexDecimal
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get field value
     *
     * @return float
     */
    public function getValue()
    {
        return $this->value;
    }
}
