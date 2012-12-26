<?php
namespace Oro\Bundle\DataModelBundle\Model;

/**
 * Abstract entity attribute option value, independent of storage
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
abstract class AbstractEntityAttributeOptionValue
{
    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var integer $option
     */
    protected $option;

    /**
     * @var string $data
     */
    protected $value;

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
     * Set id
     *
     * @param integer $id
     *
     * @return AbstractEntityAttributeOptionValue
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set option
     *
     * @param AbstractEntityAttributeOption $option
     *
     * @return AbstractEntityAttributeOptionValue
     */
    public function setOption(AbstractEntityAttributeOption $option)
    {
        $this->option = $option;

        return $this;
    }

    /**
     * Get option
     *
     * @return AbstractEntityAttributeOption
     */
    public function getOption()
    {
        return $this->option;
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

    /**
     * Set value
     *
     * @param string $value
     *
     * @return string
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
}
