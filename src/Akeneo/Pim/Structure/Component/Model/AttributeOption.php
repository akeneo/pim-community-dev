<?php

namespace Akeneo\Pim\Structure\Component\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AttributeOption implements AttributeOptionInterface
{
    protected ?int $id = null;
    protected ?string $code = null;
    protected Collection $optionValues;
    protected ?int $sortOrder = null;

    /**
     * Overrided to change target entity name
     */
    protected ?AttributeInterface $attribute = null;

    /**
     * Not persisted, allows to define the value locale
     */
    protected ?string $locale = null;

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

    public function setId($id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getAttribute(): ?AttributeInterface
    {
        return $this->attribute;
    }

    public function setAttribute(AttributeInterface $attribute = null): static
    {
        $this->attribute = $attribute;

        return $this;
    }

    public function getOptionValues(): Collection
    {
        return $this->optionValues;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale($locale): static
    {
        $this->locale = $locale;

        return $this;
    }

    public function setSortOrder($sortOrder): static
    {
        if ($sortOrder !== null) {
            $this->sortOrder = $sortOrder;
        }

        return $this;
    }

    public function getSortOrder(): ?int
    {
        return $this->sortOrder;
    }

    public function setCode($code): static
    {
        $this->code = (string) $code;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function __toString(): string
    {
        $value = $this->getOptionValue();

        return ($value && $value->getValue()) ? $value->getValue() : '[' . $this->getCode() . ']';
    }

    public function getReference(): ?string
    {
        if (null === $this->code) {
            return null;
        }

        return ($this->attribute ? $this->attribute->getCode() : '') . '.' . $this->code;
    }

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

    public function addOptionValue(AttributeOptionValueInterface $value): static
    {
        $this->optionValues[] = $value;
        $value->setOption($this);

        return $this;
    }

    public function removeOptionValue(AttributeOptionValueInterface $value): static
    {
        $this->optionValues->removeElement($value);

        return $this;
    }

    public function getOptionValue(): ?AttributeOptionValueInterface
    {
        $locale = $this->locale;
        $values = $this->optionValues->filter(
            function ($value) use ($locale): bool {
                return \strtolower($value->getLocale()) === \strtolower($locale);
            }
        );

        if ($values->isEmpty()) {
            return null;
        }

        return $values->first();
    }
}
