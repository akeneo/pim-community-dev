<?php

namespace Pim\Bundle\TranslationBundle\Entity;

use Pim\Bundle\CatalogBundle\Model\ReferableInterface;

/**
 * Default translatable entity implementation
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
trait Translatable
{
    /**
     * Used locale to override Translation listener's locale
     * this is not a mapped field of entity metadata, just a simple property
     *
     * @var string $locale
     */
    protected $locale;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection $translations
     */
    protected $translations;

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
    public function getTranslation($locale = null)
    {
        $locale = ($locale) ? $locale : $this->locale;
        if (!$locale) {
            return null;
        }
        foreach ($this->getTranslations() as $translation) {
            if ($translation->getLocale() == $locale) {
                return $translation;
            }
        }

        $translationClass = $this->getTranslationFQCN();
        $translation      = new $translationClass();
        $translation->setLocale($locale);
        $translation->setForeignKey($this);
        $this->addTranslation($translation);

        return $translation;
    }

    /**
     * {@inheritdoc}
     */
    public function addTranslation(AbstractTranslation $translation)
    {
        if (!$this->translations->contains($translation)) {
            $this->translations->add($translation);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeTranslation(AbstractTranslation $translation)
    {
        $this->translations->removeElement($translation);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslationFQCN()
    {
        return __CLASS__ . 'Translation';
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
     * Get label
     *
     * @return string
     *
     * @throws \LogicException if the class doesn't implement ReferableInterface
     */
    public function getLabel()
    {
        if (!$this instanceof ReferableInterface) {
            throw new \LogicException(
                sprintf('%s() must be implemented or class %s must implement %s!',
                    __METHOD__,
                    __CLASS__,
                    'Pim\Bundle\CatalogBundle\Model\ReferableInterface'
                )
            );
        }

        $translated = $this->getTranslation() ? $this->getTranslation()->getLabel() : null;

        return ($translated !== '' && $translated !== null) ? $translated : '['.$this->getReference().']';
    }

    /**
     * Set label
     *
     * @param string $label
     *
     * @return TranslatableInterface
     */
    public function setLabel($label)
    {
        $this->getTranslation()->setLabel($label);

        return $this;
    }

    /**
     * Returns the label of the translatable entity
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getLabel();
    }
}
