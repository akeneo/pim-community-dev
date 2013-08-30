TranslationBundle
=================

Deal with i18n datas

Install
=======
To install for dev :

```bash
$ php composer.phar update --dev
```

To use as dependency, use composer and add bundle in AppKernel :

```json
    "require": {
        [...]
        "pim/TranslationBundle": "dev-master"
    },
    "repositories": [
        {
            "type": "vcs",
            "url": "git@github.com:akeneo/TranslationBundle.git",
            "branch": "master"
        }
    ]
```


Classes / Concepts
==================
Used with gedmo doctrine extension and PIM locale entity

Example of usage
================

Define and use gedmo doctrine extension
---------------------------------------
See https://github.com/l3pp4rd/DoctrineExtensions/blob/master/doc/translatable.md

Make entity translatable and add locale property
------------------------------------------------
- Define class as translatable
- Define Translation entity
- Define fields translatable
- Add locale property and setter

```php
use Gedmo\Translatable\Translatable;

use Gedmo\Mapping\Annotation as Gedmo;

use Doctrine\ORM\Mapping as ORM;

/**
 * Translatable entity
 *
 * @ORM\Entity()
 * @ORM\Table(name="translatable_entity")
 * @Gedmo\TranslationEntity(class="MyApp\Bundle\MyBundle\Entity\TranslatableEntityTranslation")
 */
class TranslatableEntity implements Translatable
{
    // ... define properties

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=100)
     * @Gedmo\Translatable
     */
    protected $name;

    /**
     * Used locale to override Translation listener`s locale
     * this is not a mapped field of entity metadata, just a simple property
     *
     * @var string $locale
     *
     * @Gedmo\Locale
     */
    protected $locale;

    // ... define methods (getter/setter and others)

    /**
     * Define locale used by entity
     *
     * @param string $locale
     *
     * @return TranslatableEntity
     */
    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }
}
```

Create Translation entity
-------------------------
- Define class as extension of AbstractTranslation (Gedmo class)
- Redefine default repository as TranslationRepository
- To be cleaner, redefine table name and index

```php
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractTranslation;

use Doctrine\ORM\Mapping as ORM;

/**
 * Translatable entity translation
 *
 * @ORM\Entity(repositoryClass="Pim\Bundle\TranslationBundle\Entity\Repository\TranslationRepository")
 * @ORM\Table(
 *     name="translatable_entity_translations",
 *     indexes={
 *         @ORM\Index(
 *             name="translatable_entity_translations_idx",
 *             columns={"locale", "object_class", "field", "foreign_key"}
 *         )
 *     }
 * )
 *
 */
class TranslatableEntityTranslation extends AbstractTranslation
{
    /**
     * All required columns are mapped through inherited superclass
     */
}

```


Update form type
----------------
- Define translation form type for translatable field (here "name")

```php
/**
 * Translatable entity type
 */
class TranslatableEntityType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add(
            'names',
            'pim_translation_collection',
            array(
                'type' => 'pim_translation
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'MyApp\Bundle\MyBundle\Entity\TranslatableEntity'
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'translatable_entity';
    }
}
```

Call manager to save entity and translations
--------------------------------------------



Enhancements
============

- Sort by locales
- Integrate locales and currencies (not for now)
- Implements with default locale
- Create abstract translated entity
- Fix problem with default value store in translation table
- Extends entity with AbstractTranslatedClass and maybe AbstractTranslation
