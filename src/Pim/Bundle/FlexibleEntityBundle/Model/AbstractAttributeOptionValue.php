<?php

namespace Pim\Bundle\FlexibleEntityBundle\Model;

use Pim\Bundle\FlexibleEntityBundle\Model\Behavior\TranslatableInterface;

/**
 * Abstract entity attribute option value, independent of storage
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractAttributeOptionValue implements TranslatableInterface
{
    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var AbstractAttributeOption $option
     */
    protected $option;

    /**
     * @var string $value
     */
    protected $value;

    /**
     * @var string $locale
     */
    protected $locale;

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
     * @return AbstractAttributeOptionValue
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set option
     *
     * @param AbstractAttributeOption $option
     *
     * @return AbstractAttributeOptionValue
     */
    public function setOption(AbstractAttributeOption $option)
    {
        $this->option = $option;

        return $this;
    }

    /**
     * Get option
     *
     * @return AbstractAttributeOption
     */
    public function getOption()
    {
        return $this->option;
    }

    /**
     * Get used locale
     * @return string $locale
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set used locale
     * @param string $locale
     *
     * @return AbstractAttributeOptionValue
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Set value
     *
     * @param string $value
     *
     * @return AbstractAttributeOptionValue
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
