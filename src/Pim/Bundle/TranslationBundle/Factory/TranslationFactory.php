<?php

namespace Pim\Bundle\TranslationBundle\Factory;

/**
 * Translation factory for entity instanciation
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TranslationFactory
{
    /**
     * The entity translation class
     *
     * @var string
     */
    protected $translationClass;

    /**
     * Constructor
     *
     * @param string $translationClass
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($translationClass)
    {
        $refl = new \ReflectionClass($translationClass);
        if (!$refl->isSubClassOf('Pim\Bundle\TranslationBundle\Entity\AbstractTranslation')) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The translation class "%s" must extends "%s"',
                    $translationClass,
                    'Pim\Bundle\TranslationBundle\Entity'
                )
            );
        }

        $this->translationClass = $translationClass;
    }

    /**
     * Create the translation entity
     *
     * @param string $locale
     *
     * @return \Gedmo\Translatable\Entity\MappedSuperclass\AbstractTranslation
     */
    public function createTranslation($locale)
    {
        $translation = new $this->translationClass();
        $translation->setLocale($locale);

        return $translation;
    }
}
