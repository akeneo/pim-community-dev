<?php
namespace Oro\Bundle\FlexibleEntityBundle\Entity\Mapping;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\FlexibleEntityBundle\Model\Entity\AbstractEntityAttributeOption;
use Oro\Bundle\FlexibleEntityBundle\Model\Entity\AbstractEntityAttributeOptionValue;
use Oro\Bundle\FlexibleEntityBundle\Model\Behavior\TranslatableContainerInterface;

/**
 * Base Doctrine ORM entity attribute option
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT  MIT
 *
 */
abstract class AbstractOrmEntityAttributeOption extends AbstractEntityAttributeOption implements TranslatableContainerInterface
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
     * Get attribute
     *
     * @return AbstractOrmEntityAttribute
     */
    public function getAttribute()
    {
        return $this->attribute;
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
     * @return AbstractEntityAttributeOption
     */
    public function setLocaleCode($locale)
    {
        $this->localeCode = $locale;
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

    /**
     * Remove value
     *
     * @param AbstractEntityAttributeOptionValue $value
     */
    public function removeOptionValue(AbstractEntityAttributeOptionValue $value)
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
     * @return OrmEntityAttributeOptionValue
     */
    public function getOptionValue()
    {
        $attribute = $this->getAttribute();
        $locale = $this->getLocaleCode();
        $values = $this->getOptionValues()->filter(function($value) use ($attribute, $locale) {
            // return relevant translated value
            if ($attribute->getTranslatable() and $value->getLocaleCode() == $locale) {
                return true;
            } else {
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
