<?php

namespace Pim\Bundle\CatalogBundle\Entity;

use JMS\Serializer\Annotation\ExclusionPolicy;
use Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeOptionValueInterface;

/**
 * Attribute option values
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 * @ExclusionPolicy("all")
 */
class AttributeOptionValue implements AttributeOptionValueInterface
{
    /**
     * @var int $id
     */
    protected $id;

    /**
     * @var AttributeOptionInterface $option
     */
    protected $option;

    /**
     * LocaleInterface scope
     *
     * @var string $locale
     */
    protected $locale;

    /**
     * @var string $value
     */
    protected $value;

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
    public function setOption(AttributeOptionInterface $option)
    {
        $this->option = $option;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOption()
    {
        return $this->option;
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
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function setLabel($label)
    {
        $this->value = $label;

        return $this;
    }
}
