<?php
namespace Oro\Bundle\DataModelBundle\Entity;

use Oro\Bundle\DataModelBundle\Entity\AbstractOrmEntityAttributeOption;
use Doctrine\ORM\Mapping as ORM;

/**
 * Attribute options
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 * @ORM\Table(name="entity_attribute_option")
 * @ORM\Entity
 */
class OrmEntityAttributeOption extends AbstractOrmEntityAttributeOption
{

    /**
     * Overrided to change target entity name
     *
     * @var Attribute $attribute
     *
     * @ORM\ManyToOne(targetEntity="OrmEntityAttribute")
     * @ORM\JoinColumn(name="attribute_id", nullable=false, onDelete="CASCADE", referencedColumnName="id")
     */
    protected $attribute;

    /**
     * @var ArrayCollection $values
     *
     * @ORM\OneToMany(targetEntity="OrmEntityAttributeOptionValue", mappedBy="option", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $optionValues;

    /**
     * Get localized value
     *
     * @return OrmEntityAttributeOptionValue
     */
    public function getOptionValue()
    {
        $locale = $this->getLocaleCode();
        $values = $this->getOptionValues()->filter(function($value) use ($locale) {
            // return relevant translated locale
            if ($value->getLocaleCode() == $locale) {
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
