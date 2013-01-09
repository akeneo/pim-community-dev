<?php
namespace Oro\Bundle\FlexibleEntityBundle\Entity\Mapping;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\FlexibleEntityBundle\Model\Entity\AbstractAttributeOption;
use Oro\Bundle\FlexibleEntityBundle\Model\Entity\AbstractAttributeOptionValue;
use Oro\Bundle\FlexibleEntityBundle\Model\Behavior\TranslatableContainerInterface;

/**
 * Base Doctrine ORM entity attribute option
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT  MIT
 *
 */
abstract class AbstractEntityAttributeOption extends AbstractAttributeOption implements TranslatableContainerInterface
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
     * @ORM\JoinColumn(name="attribute_id", nullable=false, onDelete="CASCADE", referencedColumnName="id")
     */
    protected $attribute;

    /**
     * @ORM\Column(name="is_translatable", type="boolean")
     */
    protected $translatable;

    /**
     * Not persisted, allowe to define the value locale
     * @var string $localeCode
     */
    protected $localeCode;

    /**
     * @ORM\Column(name="sort_order", type="integer")
     */
    protected $sortOrder;

    /**
     * @var ArrayCollection $optionValues
     *
     * @ORM\OneToMany(targetEntity="AbstractEntityAttributeOptionValue", mappedBy="option", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $optionValues;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->optionValues = new \Doctrine\Common\Collections\ArrayCollection();
        $this->translatable = false;
        $this->sortOrder    = 1;
    }

    /**
     * Get attribute
     *
     * @return AbstractEntityAttribute
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * Set attribute
     *
     * @param AbstractEntityAttribute $attribute
     *
     * @return EntityAttributeOption
     */
    public function setAttribute(AbstractEntityAttribute $attribute = null)
    {
        $this->attribute = $attribute;

        return $this;
    }

    /**
     * Get used locale
     *
     * @return string $locale
     */
    public function getLocaleCode()
    {
        return $this->localeCode;
    }

    /**
     * Set used locale
     *
     * @param string $locale
     *
     * @return AbstractAttributeOption
     */
    public function setLocaleCode($locale)
    {
        $this->localeCode = $locale;
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
     */
    public function removeOptionValue(AbstractAttributeOptionValue $value)
    {
        $this->optionValues->removeElement($value);
    }

    /**
     * Get values
     *
     * @return \ArrayAccess
     */
    public function getOptionValues()
    {
        return $this->optionValues;
    }

    /**
     * Get localized value
     *
     * @return AbstractEntityAttributeOptionValue
     */
    public function getOptionValue()
    {
        $translatable = $this->translatable;
        $locale = $this->getLocaleCode();
        $values = $this->getOptionValues()->filter(function($value) use ($translatable, $locale) {
            // return relevant translated value
            if ($translatable and $value->getLocaleCode() == $locale) {
                return true;
            } else if (!$translatable) {
                return true;
            }
        });
        $value = $values->first();

        return $value;
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
