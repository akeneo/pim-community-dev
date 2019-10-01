<?php

namespace Akeneo\Pim\Structure\Component\Model;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Attribute option
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AttributeOption implements AttributeOptionInterface
{
    /** @var int */
    protected $id;

    /** @var string $code */
    protected $code;

    /**
     * Overrided to change target entity name
     *
     * @var AttributeInterface
     */
    protected $attribute;

    /** @var ArrayCollection */
    protected $optionValues;

    /**
     * Not persisted, allows to define the value locale
     *
     * @var string
     */
    protected $locale;

    /** @var int */
    protected $sortOrder;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->optionValues = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * {@inheritdoc}
     */
    public function setAttribute(AttributeInterface $attribute = null)
    {
        $this->attribute = $attribute;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptionValues()
    {
        return $this->optionValues;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setSortOrder($sortOrder)
    {
        if ($sortOrder !== null) {
            $this->sortOrder = $sortOrder;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    /**
     * {@inheritdoc}
     */
    public function setCode($code)
    {
        $this->code = (string) $code;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Override to use default value
     *
     * {@inheritdoc}
     */
    public function __toString()
    {
        $value = $this->getOptionValue();

        return ($value && $value->getValue()) ? $value->getValue() : '['.$this->getCode().']';
    }

    /**
     * {@inheritdoc}
     */
    public function getReference()
    {
        if (null === $this->code) {
            return null;
        }

        return ($this->attribute ? $this->attribute->getCode() : '') . '.' . $this->code;
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslation()
    {
        $value = $this->getOptionValue();

        if (!$value) {
            $value = new AttributeOptionValue();
            $value->setLocale($this->locale);
            $this->addOptionValue($value);
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function addOptionValue(AttributeOptionValueInterface $value)
    {
        $this->optionValues[] = $value;
        $value->setOption($this);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeOptionValue(AttributeOptionValueInterface $value)
    {
        $this->optionValues->removeElement($value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptionValue()
    {
        $locale = $this->locale;
        $values = $this->getOptionValues()->filter(
            function ($value) use ($locale) {
                if ($value->getLocale() === $locale) {
                    return true;
                }
            }
        );

        if ($values->isEmpty()) {
            return null;
        }

        return $values->first();
    }
}
