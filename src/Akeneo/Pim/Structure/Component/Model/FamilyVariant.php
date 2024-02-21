<?php

namespace Akeneo\Pim\Structure\Component\Model;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Component\Localization\Model\TranslationInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Webmozart\Assert\Assert;

/**
 * A variant in a family defines the structure for the products with variants:
 * Common attributes, Specific or variant attributes, Variant axes.
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyVariant implements FamilyVariantInterface
{
    /** @var int */
    private $id;

    /** @var string */
    private $code;

    /** @var FamilyInterface */
    private $family;

    /** @var Collection */
    private $variantAttributeSets;

    /** @var string */
    private $locale;

    /** @var Collection */
    private $translations;

    /** @var string[] */
    private array $events = [];

    public function __construct()
    {
        $this->variantAttributeSets = new ArrayCollection();
        $this->translations = new ArrayCollection();
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
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * {@inheritdoc}
     */
    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    /**
     * {@inheritdoc}
     */
    public function getCommonAttributes(): CommonAttributeCollection
    {
        $commonAttributes = CommonAttributeCollection::fromCollection($this->family->getAttributes());

        foreach ($this->variantAttributeSets as $variantAttributeSet) {
            foreach ($variantAttributeSet->getAxes() as $axis) {
                $commonAttributes->removeElement($axis);
            }

            foreach ($variantAttributeSet->getAttributes() as $attribute) {
                $commonAttributes->removeElement($attribute);
            }
        }

        return $commonAttributes;
    }

    /**
     * {@inheritdoc}
     */
    public function getVariantAttributeSet(int $level): ?VariantAttributeSetInterface
    {
        if (0 >= $level) {
            throw new \InvalidArgumentException('The level must be greater than 0');
        }

        foreach ($this->variantAttributeSets as $variantAttributeSet) {
            if ($level === $variantAttributeSet->getLevel()) {
                return $variantAttributeSet;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getVariantAttributeSets(): Collection
    {
        return $this->variantAttributeSets;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes(): Collection
    {
        $attributes = [];
        foreach ($this->variantAttributeSets as $attributeSet) {
            $variantAttributeSetAttributes = $attributeSet->getAttributes()->toArray();
            $attributes = array_merge($attributes, $variantAttributeSetAttributes);
        }

        return new ArrayCollection($attributes);
    }

    /**
     * {@inheritdoc}
     */
    public function getAxes(): Collection
    {
        $axes = [];
        foreach ($this->variantAttributeSets as $attributeSet) {
            $variantSetAxes = $attributeSet->getAxes()->toArray();
            $axes = array_merge($axes, $variantSetAxes);
        }

        return new ArrayCollection($axes);
    }

    /**
     * {@inheritdoc}
     */
    public function addVariantAttributeSet(VariantAttributeSetInterface $variantAttributeSet): void
    {
        $this->variantAttributeSets->add($variantAttributeSet);
    }

    /**
     * {@inheritdoc}
     */
    public function updateAxesForLevel(int $level, array $axes): void
    {
        Assert::allIsInstanceOf($axes, AttributeInterface::class);
        if (null === $attributeSet = $this->getVariantAttributeSet($level)) {
            $attributeSet = new VariantAttributeSet();
            $attributeSet->setLevel($level);
            $this->addVariantAttributeSet($attributeSet);
            $formerAxeIds = [];
        } else {
            $formerAxeIds = $attributeSet->getAxes()->map(
                static fn (AttributeInterface $attribute): int => $attribute->getId() ?? 0
            )->toArray();
        }

        $newAxeIds = \array_map(
            static fn (AttributeInterface $attribute): int => $attribute->getId() ?? 0,
            $axes
        );
        sort($formerAxeIds);
        sort($newAxeIds);

        if ($formerAxeIds !== $newAxeIds) {
            $this->events[] = FamilyVariantInterface::AXES_WERE_UPDATED_ON_LEVEL;
            $attributeSet->setAxes($axes);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function updateAttributesForLevel(int $level, array $attributes): void
    {
        Assert::allIsInstanceOf($attributes, AttributeInterface::class);
        if (null === $attributeSet = $this->getVariantAttributeSet($level)) {
            $attributeSet = new VariantAttributeSet();
            $attributeSet->setLevel($level);
            $this->addVariantAttributeSet($attributeSet);
            $formerAttributeIds = [];
        } else {
            $formerAttributeIds = $attributeSet->getAttributes()->map(
                static fn (AttributeInterface $attribute): int => $attribute->getId() ?? 0
            )->toArray();
        }

        $newAttributeIds = \array_map(
            static fn (AttributeInterface $attribute): int => $attribute->getId() ?? 0,
            $attributes
        );
        sort($formerAttributeIds);
        sort($newAttributeIds);

        if ($formerAttributeIds !== $newAttributeIds) {
            $this->events[] = FamilyVariantInterface::ATTRIBUTES_WERE_UPDATED_ON_LEVEL;
            $attributeSet->setAttributes($attributes);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getFamily(): ?FamilyInterface
    {
        return $this->family;
    }

    /**
     * {@inheritdoc}
     */
    public function setFamily(FamilyInterface $family): void
    {
        $this->family = $family;
    }

    /**
     * {@inheritdoc}
     */
    public function getNumberOfLevel(): int
    {
        return $this->variantAttributeSets->count();
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
    public function getTranslation(?string $locale = null)
    {
        $locale = $locale ?: $this->locale;
        if (null === $locale) {
            return null;
        }
        foreach ($this->getTranslations() as $translation) {
            if (\strtolower($translation->getLocale()) === \strtolower($locale)) {
                return $translation;
            }
        }

        $translationClass = $this->getTranslationFQCN();
        $translation = new $translationClass();
        $translation->setLocale($locale);
        $translation->setForeignKey($this);
        $this->addTranslation($translation);

        return $translation;
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * {@inheritdoc}
     */
    public function addTranslation(TranslationInterface $translation)
    {
        if (!$this->translations->contains($translation)) {
            $this->translations->add($translation);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeTranslation(TranslationInterface $translation)
    {
        $this->translations->removeElement($translation);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslationFQCN()
    {
        return FamilyVariantTranslation::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getLevelForAttributeCode(string $attributeCode): int
    {
        if (!$this->getFamily()->hasAttributeCode($attributeCode)) {
            throw new \InvalidArgumentException(sprintf(
                'Impossible to get variation level for attribute "%s", as family "%s" does not contain it.',
                $attributeCode,
                $this->getFamily()->getCode()
            ));
        }

        $level = 0;

        foreach ($this->variantAttributeSets as $attributeSet) {
            $variantAttributeSetHasAttribute = false;

            foreach ($attributeSet->getAttributes() as $attribute) {
                if ($attribute->getCode() === $attributeCode) {
                    $variantAttributeSetHasAttribute = true;
                    break;
                }
            }

            if ($variantAttributeSetHasAttribute) {
                $level = $attributeSet->getLevel();
                break;
            }
        }

        return $level;
    }

    /**
     * {@inheritdoc}
     */
    public static function getAvailableAxesAttributeTypes(): array
    {
        return [
            AttributeTypes::METRIC,
            AttributeTypes::OPTION_SIMPLE_SELECT,
            AttributeTypes::BOOLEAN,
            AttributeTypes::REFERENCE_DATA_SIMPLE_SELECT,
            AttributeTypes::REFERENCE_ENTITY_SIMPLE_SELECT,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function releaseEvents(): array
    {
        $events = $this->events;
        $this->events = [];

        return $events;
    }

    public function addEvent(string $eventName): void
    {
        $this->events[] = $eventName;
    }
}
