<?php

namespace Akeneo\Channel\Component\Model;

use Akeneo\Tool\Component\Localization\Model\TranslatableInterface;
use Akeneo\Tool\Component\Localization\Model\AbstractTranslation;
use Akeneo\Channel\Component\Event\ChannelCategoryHasBeenUpdated;
use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Tool\Component\Localization\Model\TranslationInterface;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Channel entity
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Channel implements ChannelInterface
{
    /** @var int $id */
    protected $id;

    /** @var string $code */
    protected $code;

    /** @var CategoryInterface $category */
    protected $category;

    /** @var ArrayCollection $currencies */
    protected $currencies;

    /** @var ArrayCollection $locales */
    protected $locales;

    /**
     * Used locale to override Translation listener's locale
     * this is not a mapped field of entity metadata, just a simple property
     *
     * @var string
     */
    protected $locale;

    /** @var ChannelTranslation[] */
    protected $translations;

    /** @var array $conversionUnits */
    protected $conversionUnits = [];

    /** @var array|ChannelEvent[] */
    private $events = [];

    public function __construct()
    {
        $this->currencies = new ArrayCollection();
        $this->locales = new ArrayCollection();
        $this->translations = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set id
     *
     * @param int $id
     */
    public function setId(int $id): self
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
    public function setCode(string $code): ChannelInterface
    {
        $this->code = $code;

        return $this;
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
    public function getTranslation(?string $locale = null): AbstractTranslation
    {
        $locale = $locale ? $locale : $this->locale;
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
    public function getTranslations(): \Doctrine\Common\Collections\ArrayCollection
    {
        return $this->translations;
    }

    /**
     * {@inheritdoc}
     */
    public function addTranslation(TranslationInterface $translation): TranslatableInterface
    {
        if ($this->translations->contains($translation) === []) {
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
        return ChannelTranslation::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        $translated = ($this->getTranslation()) ? $this->getTranslation()->getLabel() : null;

        return ($translated !== '' && $translated !== null) ? $translated : '['.$this->getCode().']';
    }

    /**
     * {@inheritdoc}
     */
    public function setLabel(string $label): ChannelInterface
    {
        $this->getTranslation()->setLabel($label);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCategory(): \Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface
    {
        return $this->category;
    }

    /**
     * {@inheritdoc}
     */
    public function setCategory(CategoryInterface $category): ChannelInterface
    {
        if ($this->category === null) {
            $this->category = $category;

            return $this;
        }

        if ($this->category->getCode() !== $category->getCode()) {
            $previousCategoryCode = $this->category->getCode();
            $this->category = $category;
            $this->addEvent(new ChannelCategoryHasBeenUpdated($this->code, $previousCategoryCode, $category->getCode()));
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrencies(): \Doctrine\Common\Collections\ArrayCollection
    {
        return $this->currencies;
    }

    /**
     * {@inheritdoc}
     */
    public function setCurrencies(array $currencies): void
    {
        foreach ($this->currencies as $currency) {
            if (!in_array($currency, $currencies)) {
                $this->removeCurrency($currency);
            }
        }

        foreach ($currencies as $currency) {
            $this->addCurrency($currency);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addCurrency(CurrencyInterface $currency): ChannelInterface
    {
        if (!$this->hasCurrency($currency)) {
            $this->currencies[] = $currency;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeCurrency(CurrencyInterface $currency): ChannelInterface
    {
        $this->currencies->removeElement($currency);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocales(): \Doctrine\Common\Collections\ArrayCollection
    {
        return $this->locales;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocaleCodes(): array
    {
        return $this->locales->map(
            fn($locale) => $locale->getCode()
        )->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function setLocales(array $locales): void
    {
        foreach ($this->locales as $locale) {
            if (!in_array($locale, $locales)) {
                $this->removeLocale($locale);
            }
        }

        foreach ($locales as $locale) {
            $this->addLocale($locale);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addLocale(LocaleInterface $locale): ChannelInterface
    {
        if (!$this->hasLocale($locale)) {
            $this->locales[] = $locale;
            $locale->addChannel($this);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeLocale(LocaleInterface $locale): ChannelInterface
    {
        $this->locales->removeElement($locale);
        $locale->removeChannel($this);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasLocale(LocaleInterface $locale): bool
    {
        return $this->locales->contains($locale);
    }

    /**
     * {@inheritdoc}
     */
    public function hasCurrency(CurrencyInterface $currency): bool
    {
        return $this->currencies->contains($currency);
    }

    /**
     * {@inheritdoc}
     */
    public function setConversionUnits(array $conversionUnits): ChannelInterface
    {
        $this->conversionUnits = $conversionUnits;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getConversionUnits(): array
    {
        return $this->conversionUnits;
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
    public function getReference(): string
    {
        return $this->code;
    }

    /**
     * @return array|ChannelEvent[]
     */
    public function popEvents(): array
    {
        $events = $this->events;
        $this->events = [];

        return $events;
    }

    private function addEvent($event): void
    {
        $this->events[] = $event;
    }
}
