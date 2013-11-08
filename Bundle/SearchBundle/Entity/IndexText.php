<?php

namespace Oro\Bundle\SearchBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Text entity for search index
 *
 * @ORM\Table(name="oro_search_index_text", options={"engine"="MyISAM"})
 * @ORM\Entity
 */
class IndexText
{
    const HYPHEN_SUBSTITUTION = '__HYPHEN__';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Item", inversedBy="textFields")
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
     * @var string
     *
     * @ORM\Column(name="value", type="text", nullable=false)
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
     * Set field name
     *
     * @param  string    $field
     * @return IndexText
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
     * @param  string    $value
     * @return IndexText
     */
    public function setValue($value)
    {
        $processedValue = str_replace('-', self::HYPHEN_SUBSTITUTION, $value);

        $this->value = $processedValue;

        return $this;
    }

    /**
     * Get field value
     *
     * @return string
     */
    public function getValue()
    {
        if (null !== $this->value) {
            $originalValue = str_replace(self::HYPHEN_SUBSTITUTION, '-', $this->value);
        } else {
            $originalValue = null;
        }

        return $originalValue;
    }

    /**
     * Set item
     *
     * @param  Item      $item
     * @return IndexText
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
}
