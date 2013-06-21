<?php

namespace Pim\Bundle\TranslationBundle\Factory;

use Gedmo\Translatable\Entity\MappedSuperclass\AbstractTranslation;

/**
 * Translation factory for entity instanciation
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
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
     * The entity class
     *
     * @var string
     */
    protected $entityClass;

    /**
     * The field type
     *
     * @var string
     */
    protected $field;

    /**
     * Constructor
     *
     * @param string $translationClass
     * @param string $entityClass
     * @param string $field
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($translationClass, $entityClass, $field)
    {
        $refl = new \ReflectionClass($translationClass);
        if (!$refl->isSubClassOf('Gedmo\Translatable\Entity\MappedSuperclass\AbstractTranslation')) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The translation class "%s" must extends Gedmo\Translatable\Entity\MappedSuperclass\AbstractTranslation',
                    $translationClass
                )
            );
        }

        $this->translationClass = $translationClass;
        $this->entityClass      = $entityClass;
        $this->field            = $field;
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
        $translation->setObjectClass($this->entityClass);
        $translation->setField($this->field);

        return $translation;
    }
}
