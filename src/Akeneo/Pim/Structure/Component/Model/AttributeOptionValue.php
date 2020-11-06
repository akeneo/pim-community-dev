<?php

namespace Akeneo\Pim\Structure\Component\Model;

/**
 * Attribute option values
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AttributeOptionValue implements AttributeOptionValueInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var AttributeOptionInterface
     */
    protected $option;

    /**
     * LocaleInterface scope
     *
     * @var string
     */
    protected $locale;

    /**
     * @var string
     */
    protected $value;

    /**
     * {@inheritdoc}
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function setId(int $id): AttributeOptionValueInterface
    {
        $this->id = $id;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setOption(AttributeOptionInterface $option): AttributeOptionValueInterface
    {
        $this->option = $option;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOption(): \Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface
    {
        return $this->option;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale(string $locale): AttributeOptionValueInterface
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setValue(string $value): AttributeOptionValueInterface
    {
        $this->value = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function setLabel(string $label): AttributeOptionValueInterface
    {
        $this->value = (string) $label;

        return $this;
    }
}
