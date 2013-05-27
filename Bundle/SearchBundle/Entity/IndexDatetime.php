<?php

namespace Oro\Bundle\SearchBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Date time entity for search index
 *
 * @ORM\Table(name="oro_search_index_datetime")
 * @ORM\Entity
 */
class IndexDatetime
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Item", inversedBy="datetimeFields")
     * @ORM\JoinColumn(name="item_id", referencedColumnName="id", nullable=false)
     */
    protected $item;

    /**
     * @var string
     *
     * @ORM\Column(name="field", type="string", length=250, nullable=false)
     */
    protected $field;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="value", type="datetime", nullable=false)
     */
    protected $value;

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
     * @param  Item          $item
     * @return IndexDatetime
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
     * @param  string        $field
     * @return IndexDatetime
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
     * @param  \DateTime     $value
     * @return IndexDatetime
     */
    public function setValue(\DateTime $value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get field value
     *
     * @return \DateTime
     */
    public function getValue()
    {
        return $this->value;
    }
}
