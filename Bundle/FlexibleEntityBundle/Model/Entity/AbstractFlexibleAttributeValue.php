<?php
namespace Oro\Bundle\FlexibleEntityBundle\Model\Entity;

/**
 * Abstract entity value, independent of storage
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
abstract class AbstractFlexibleAttributeValue
{

    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var EntityAttribute $attribute
     */
    protected $attribute;

    /**
     * @var mixed $data
     */
    protected $data;

    /**
     * @var string $localeCode
     */
    protected $localeCode;

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
     * Set data
     *
     * @param string $data
     *
     * @return AbstractFlexibleAttributeValue
     */
     public function setData($data)
     {
         $this->data = $data;

         return $this;
     }

    /**
     * Get data
     *
     * @return string
     */
     public function getData()
     {
         return $this->data;
     }

    /**
     * Set attribute
     *
     * @param EntityAttribute $attribute
     *
     * @return AbstractFlexibleAttributeValue
     */
    public function setAttribute(AbstractAttribute $attribute = null)
    {
        $this->attribute = $attribute;

        return $this;
    }

    /**
     * Get attribute
     *
     * @return AbstractFlexibleAttributeValue
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * Get used locale
     * @return string $locale
     */
    public function getLocaleCode()
    {
        return $this->localeCode;
    }

    /**
     * Set used locale
     * @param string $locale
     */
    public function setLocaleCode($locale)
    {
        $this->localeCode = $locale;
    }
}
