<?php

namespace Akeneo\Pim\Structure\Component\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantAttributeSet implements VariantAttributeSetInterface
{
    /** @var int */
    private $id;

    /** @var Collection */
    private $attributes;

    /** @var Collection */
    private $axes;

    /** @var int */
    private $level;

    public function __construct()
    {
        $this->attributes = new ArrayCollection();
        $this->axes = new ArrayCollection();
    }

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
    public function getAttributes(): Collection
    {
        return $this->attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function hasAttribute(AttributeInterface $attribute): bool
    {
        return $this->containsAttribute($this->attributes, $attribute);
    }

    /**
     * {@inheritdoc}
     */
    public function addAttribute(AttributeInterface $attribute): void
    {
        if (!$this->hasAttribute($attribute)) {
            $this->attributes->add($attribute);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setAttributes(array $attributes): void
    {
        $this->attributes = new ArrayCollection($attributes);

        foreach ($this->axes as $axis) {
            $this->addAttribute($axis);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAxes(): Collection
    {
        return $this->axes;
    }

    /**
     * {@inheritdoc}
     */
    public function setAxes(array $axes): void
    {
        foreach ($this->axes as $axis) {
            if ($this->hasAttribute($axis)) {
                $this->attributes->removeElement($axis);
            }
        }

        $this->axes = new ArrayCollection($axes);

        foreach ($this->axes as $axis) {
            $this->addAttribute($axis);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * {@inheritdoc}
     */
    public function setLevel(int $level): void
    {
        $this->level = $level;
    }

    /**
     * {@inheritdoc}
     */
    public function getAxesLabels(string $localeCode): array
    {
        $labels = [];

        foreach ($this->axes as $axis) {
            $axis->setLocale($localeCode);
            $labels[] = $axis->getLabel();
        }

        return $labels;
    }

    private function containsAttribute(Collection $attributes, AttributeInterface $attribute): bool
    {
        return $attributes->exists(function ($key, $element) use ($attribute) {
            return $element->getCode() === $attribute->getCode();
        });
    }
}
