<?php
namespace Oro\Bundle\DataModelBundle\Model;

/**
 * Abstract entity, independent of storage
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
abstract class AbstractEntity
{

    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var datetime $created
     */
    protected $created;

    /**
     * @var datetime $created
     */
    protected $updated;

    /**
     * @var string $defaultLocaleCode
     */
    protected $defaultlocaleCode;

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
     * Get created datetime
     *
     * @return datetime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Get updated datetime
     *
     * @return datetime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Add value
     *
     * @param EntityAttributeValue $value
     *
     * @return AbstractEntity
     */
    public function addValue(AbstractEntityAttributeValue $value)
    {
        $this->values[] = $value;

        return $this;
    }

    /**
     * Remove value
     *
     * @param EntityAttributeValue $value
     */
    public function removeValue(AbstractEntityAttributeValue $value)
    {
        $this->values->removeElement($value);
    }

    /**
     * Get values
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getValues()
    {
        return $this->values;
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
     *
     * @return AbstractEntity
     */
    public function setLocaleCode($locale)
    {
        $this->localeCode = $locale;

        return $this;
    }

    /**
     * Get default locale code
     *
     * @return string
     */
    public function getDefaultLocaleCode()
    {
        return $this->defaultlocaleCode;
    }

    /**
     * Set locale code
     *
     * @param string $code
     *
     * @return AbstractEntity
     */
    public function setDefaultLocaleCode($code)
    {
        $this->defaultlocaleCode = $code;

        return $this;
    }
}
