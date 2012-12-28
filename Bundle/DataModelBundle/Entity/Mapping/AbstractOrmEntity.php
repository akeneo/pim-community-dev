<?php
namespace Oro\Bundle\DataModelBundle\Entity\Mapping;

use Oro\Bundle\DataModelBundle\Model\Entity\AbstractEntity;
use Oro\Bundle\DataModelBundle\Model\Entity\AbstractEntityAttributeValue;
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
     * Add value, override to deal with relation owner side
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
     * Get value related to attribute code
     *
     * @param string $attributeCode
     *
     * @return mixed|NULL
     */
    public function getValue($attributeCode)
    {
        $locale = $this->getLocaleCode();
        $defaultLocale = $this->getDefaultLocaleCode();
        $values = $this->getValues()->filter(function($value) use ($attributeCode, $locale, $defaultLocale) {
            // related value to asked attribute
            if ($value->getAttribute()->getCode() == $attributeCode) {
                // return relevant translated locale
                if ($value->getAttribute()->getTranslatable() and $value->getLocaleCode() == $locale) {
                    return true;
                }
                // default value if not translatable
                if (!$value->getAttribute()->getTranslatable() and $value->getLocaleCode() == $defaultLocale) {
                    return true;
                }
            }

            return false;
        });
        $value = $values->first();

        return $value;
    }

    /**
     * Get value data (string, number, etc) related to attribute code
     *
     * @param string $attributeCode
     *
     * @return mixed|NULL
     */
    public function getValueData($attributeCode)
    {
        $value = $this->getValue($attributeCode);

        return ($value) ? $value->getData() : null;
    }

    /**
     * Check if a field or attribute exists
     *
     * @param string $name
     *
     * @return boolean
     */
    public function __isset($name)
    {
        // TODO authorize call to dynamic __get by twig, should be filter on existing attributes
        // cf http://twig.sensiolabs.org/doc/recipes.html#using-dynamic-object-properties
        return true;
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
        // call existing getAttCode method
        $methodName = "get{$attCode}";
        if (method_exists($this, $methodName)) {
            return $this->$methodName();
        // dynamic call to get value data
        } else {
            return $this->getValueData($attCode);
        }
    }

}
