<?php

namespace Akeneo\Pim\Structure\Component\Model;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AttributeOption implements AttributeOptionInterface
{
    protected ?int $id;
    protected ?string $code;
    protected ArrayCollection $optionValues;
    protected ?int $sortOrder;

    /**
     * Overrided to change target entity name
     */
    protected ?AttributeInterface $attribute;

    /**
     * Not persisted, allows to define the value locale
     */
    protected ?string $locale;

    public function __construct()
    {
        $this->optionValues = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id): static
    {
        $this->id = $id;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttribute(): AttributeInterface
    {
        return $this->attribute;
    }

    /**
     * {@inheritdoc}
     */
    public function setAttribute(AttributeInterface $attribute = null): static
    {
        $this->attribute = $attribute;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptionValues(): ArrayCollection
    {
        return $this->optionValues;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale(): ?string
    {
        return $this->locale;
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale($locale): static
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setSortOrder($sortOrder): static
    {
        if ($sortOrder !== null) {
            $this->sortOrder = $sortOrder;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSortOrder(): ?int
    {
        return $this->sortOrder;
    }

    /**
     * {@inheritdoc}
     */
    public function setCode($code): static
    {
        $this->code = (string) $code;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * Override to use default value
     *
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        $value = $this->getOptionValue();

        return ($value && $value->getValue()) ? $value->getValue() : '[' . $this->getCode() . ']';
    }

    /**
     * {@inheritdoc}
     */
    public function getReference(): ?string
    {
        if (null === $this->code) {
            return null;
        }

        return ($this->attribute ? $this->attribute->getCode() : '') . '.' . $this->code;
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslation(): ?AttributeOptionValueInterface
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
    public function addOptionValue(AttributeOptionValueInterface $value): static
    {
        $this->optionValues[] = $value;
        $value->setOption($this);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeOptionValue(AttributeOptionValueInterface $value): static
    {
        $this->optionValues->removeElement($value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptionValue(): ?AttributeOptionValueInterface
    {
        $locale = $this->locale;
        $values = $this->optionValues->filter(
            function ($value) use ($locale): bool {
                return $value->getLocale() === $locale;
            }
        );

        if ($values->isEmpty()) {
            return null;
        }

        return $values->first();
    }
}
