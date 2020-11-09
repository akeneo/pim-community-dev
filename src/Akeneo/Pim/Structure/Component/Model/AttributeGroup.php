<?php

namespace Akeneo\Pim\Structure\Component\Model;

use Akeneo\Tool\Component\Versioning\Model\TimestampableInterface;
use Akeneo\Tool\Component\Localization\Model\TranslatableInterface;
use Akeneo\Tool\Component\Localization\Model\AbstractTranslation;
use Akeneo\Tool\Component\Localization\Model\TranslationInterface;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Attribute Group entity
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeGroup implements AttributeGroupInterface
{
    /** @staticvar string */
    const DEFAULT_GROUP_CODE = 'other';

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $code;

    /**
     * @var int
     */
    protected $sortOrder;

    /**
     * @var \DateTime
     */
    protected $created;

    /**
     * @var \DateTime
     */
    protected $updated;

    /**
     * @var ArrayCollection
     */
    protected $attributes;

    /**
     * Used locale to override Translation listener's locale
     * this is not a mapped field of entity metadata, just a simple property
     *
     * @var string
     */
    protected $locale;

    /**
     * @var ArrayCollection
     */
    protected $translations;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->attributes = new ArrayCollection();
        $this->translations = new ArrayCollection();
        $this->sortOrder = 0;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->getLabel();
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
    public function setId(int $id): AttributeGroupInterface
    {
        $this->id = $id;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * {@inheritdoc}
     */
    public function setCode(string $code): AttributeGroupInterface
    {
        $this->code = $code;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    /**
     * {@inheritdoc}
     */
    public function setSortOrder(string $sortOrder): AttributeGroupInterface
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreated(): \DateTime
    {
        return $this->created;
    }

    /**
     * {@inheritdoc}
     */
    public function setCreated(\DateTime $created): TimestampableInterface
    {
        $this->created = $created;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdated(): \DateTime
    {
        return $this->updated;
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdated(\DateTime $updated): TimestampableInterface
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addAttribute(AttributeInterface $attribute): AttributeGroupInterface
    {
        $this->attributes[] = $attribute;
        $attribute->setGroup($this);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeAttribute(AttributeInterface $attribute): AttributeGroupInterface
    {
        $this->attributes->removeElement($attribute);
        $attribute->setGroup(null);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes(): \Doctrine\Common\Collections\ArrayCollection
    {
        return $this->attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function hasAttribute(AttributeInterface $attribute): bool
    {
        return $this->attributes->contains($attribute);
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxAttributeSortOrder(): int
    {
        $max = 0;
        foreach ($this->getAttributes() as $att) {
            $max = max($att->getSortOrder(), $max);
        }

        return $max;
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale(?string $locale): TranslatableInterface
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslations(): \Doctrine\Common\Collections\ArrayCollection
    {
        return $this->translations;
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslation(?string $locale = null): AbstractTranslation
    {
        $locale = ($locale) ? $locale : $this->locale;
        if (null === $locale) {
            return null;
        }
        foreach ($this->getTranslations() as $translation) {
            if ($translation->getLocale() === $locale) {
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
    public function addTranslation(TranslationInterface $translation): TranslatableInterface
    {
        if (!$this->translations->contains($translation)) {
            $this->translations->add($translation);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeTranslation(TranslationInterface $translation): TranslatableInterface
    {
        $this->translations->removeElement($translation);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslationFQCN(): string
    {
        return AttributeGroupTranslation::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        $translated = $this->getTranslation() !== null ? $this->getTranslation()->getLabel() : null;

        return ($translated !== '' && $translated !== null) ? $translated : '['. $this->getCode() .']';
    }

    /**
     * {@inheritdoc}
     */
    public function setLabel(string $label): AttributeGroupInterface
    {
        $this->getTranslation()->setLabel($label);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getReference(): string
    {
        return $this->code;
    }
}
