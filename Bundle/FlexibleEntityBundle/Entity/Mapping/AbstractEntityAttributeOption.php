<?php

namespace Oro\Bundle\FlexibleEntityBundle\Entity\Mapping;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeOption;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeOptionValue;

/**
 * Base Doctrine ORM entity attribute option
 */
abstract class AbstractEntityAttributeOption extends AbstractAttributeOption
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
     * @ORM\ManyToOne(targetEntity="AbstractEntityAttribute")
     */
    protected $attribute;

    /**
     * @ORM\Column(name="is_translatable", type="boolean")
     */
    protected $translatable;

    /**
     * @ORM\Column(name="sort_order", type="integer")
     */
    protected $sortOrder;

    /**
     * @var string $defaultValue
     *
     * @ORM\Column(name="default_value", type="string", length=255, nullable=true)
     */
    protected $defaultValue;

    /**
     * @var ArrayCollection $optionValues
     *
     * @ORM\OneToMany(
     *     targetEntity="AbstractEntityAttributeOptionValue",
     *     mappedBy="option",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true
     * )
     */
    protected $optionValues;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->optionValues = new ArrayCollection();
        $this->translatable = true;
        $this->sortOrder    = 1;
    }

    /**
     * Add option value
     *
     * @param AbstractAttributeOptionValue $value
     *
     * @return AbstractAttribute
     */
    public function addOptionValue(AbstractAttributeOptionValue $value)
    {
        $this->optionValues[] = $value;
        $value->setOption($this);

        return $this;
    }

    /**
     * Remove value
     *
     * @param AbstractAttributeOptionValue $value
     *
     * @return AbstractAttributeOption
     */
    public function removeOptionValue(AbstractAttributeOptionValue $value)
    {
        $this->optionValues->removeElement($value);

        return $this;
    }

    /**
     * Get localized value
     *
     * @return AbstractEntityAttributeOptionValue
     */
    public function getOptionValue()
    {
        $translatable = $this->translatable;
        $locale = $this->getLocale();
        $values = $this->getOptionValues()->filter(
            function ($value) use ($translatable, $locale) {
                // return relevant translated value
                if ($translatable and $value->getLocale() == $locale) {
                    return true;
                } elseif (!$translatable) {
                    return true;
                }
            }
        );
        $value = $values->first();

        return $value;
    }

    /**
     * Set defaultValue
     *
     * @param string $defaultValue
     *
     * @return AbstractAttributeOption
     */
    public function setDefaultValue($defaultValue)
    {
        $this->defaultValue = $defaultValue;

        return $this;
    }

    /**
     * Get defaultValue
     *
     * @return string
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * To string
     *
     * @return string
     */
    public function __toString()
    {
        $value = $this->getOptionValue();

        return ($value) ? $value->getValue() : '';
    }
}
