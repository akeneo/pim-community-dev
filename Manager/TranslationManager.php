<?php
namespace Pim\Bundle\TranslationBundle\Manager;

use Gedmo\Tool\Wrapper\EntityWrapper;

use Doctrine\ORM\EntityManager;

use Doctrine\Common\Persistence\ObjectManager;

/**
 * Only work for entities for now (no documents)
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class TranslationManager
{

    /**
     * @var EntityManager
     */
    protected $objectManager;

    /**
     * @var multitype:string
     */
    protected $activeLocales = array();

    /**
     * Constructor
     *
     * @param EntityManager $objectManager
     */
    public function __construct(EntityManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Set active locales
     *
     * @param multitype:string $locales
     *
     * @return TranslationManager
     */
    public function setActiveLocales($locales)
    {
        $this->activeLocales = $locales;

        return $this;
    }

    /**
     * Get translated object
     *
     * @param \Gedmo\Translatable\Translatable $entity    Doctrine entity implementing Translatable interface
     * @param string                           $i18nClass FQCN for translation entity
     * @param string                           $field     field name translated
     * @param boolean                          $enrich    populate with all defined locales or not
     *
     * @return multitype:AbstractTranslation
     */
    public function getTranslatedObjects($entity, $i18nClass, $field, $enrich = true)
    {
        $wrapped = new EntityWrapper($entity, $this->objectManager);

        if ($wrapped->hasValidIdentifier()) {
            $i18nRepo = $this->objectManager->getRepository($i18nClass);

            if ($i18nRepo instanceof \Pim\Bundle\TranslationBundle\Entity\Repository\TranslationRepository) {
                $i18nEntities = $i18nRepo->findTranslatedObjects($entity, $field, $this->activeLocales);

                if ($enrich) {
                    $i18nEntities = $this->enrich($i18nEntities, $wrapped, $i18nClass, $field);
                }

                return $i18nEntities;
            } else {
                // Not the good translation repository
                throw new \Exception('Wrong repository');
            }
        } else {
            throw new \Exception('Entity mis wrapped');
        }
    }

    /**
     * Enrich collection of entities with locales not already defined
     *
     * @param multitype:AbstractTranslation $i18nEntities translated entities collection
     * @param EntityWrapper                 $wrapper      entity wrapper
     * @param string                        $i18nClass    class name for translated entities
     * @param string                        $field        field to translate
     *
     * @return multitype:AbstractTranslation
     */
    protected function enrich($i18nEntities, EntityWrapper $wrapper, $i18nClass, $field)
    {
        $activeLocales = $this->activeLocales;

        foreach ($activeLocales as $locale) {
            $present = false;
            foreach ($i18nEntities as $i18nEntity) {
                if ($i18nEntity->getLocale() === $locale) {
                    $present = true;
                }
            }
            if ($present === false) {
                $i18nEntity = new $i18nClass();
                $i18nEntity->setField($field);
                $i18nEntity->setForeignKey($wrapper->getObject()->getId());
                $i18nEntity->setLocale($locale);
                $i18nEntity->setObjectClass($wrapper->getMetadata()->rootEntityName);

                $i18nEntities[] = $i18nEntity;
            }
        }

        return $i18nEntities;
    }

    /**
     * Order locales
     *
     * @param multitype $i18nEntities
     *
     * @return multitype
     */
    protected function orderByLocales($i18nEntities)
    {
        return $i18nEntities;
    }

    /**
     * Persist translated entities
     *
     * @param mixed $i18nEntity
     */
    public function persist($i18nEntity)
    {
        if (is_array($i18nEntity)) {
            foreach ($i18nEntity as $entity) {
                $this->objectManager->persist($entity);
            }
        } else {
            $this->objectManager->persist($i18nEntity);
        }
    }
}
