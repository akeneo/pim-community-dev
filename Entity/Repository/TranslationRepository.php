<?php
namespace Pim\Bundle\TranslationBundle\Entity\Repository;

use Doctrine\ORM\Query;

use Gedmo\Tool\Wrapper\EntityWrapper;

use Gedmo\Translatable\Entity\Repository\TranslationRepository as GedmoTranslationRepository;

/**
 * Extended Gedmo TranslationRepository
 * For now, only work with EntityTranslation entity
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class TranslationRepository extends GedmoTranslationRepository
{

    /**
     * Find translation entities
     *
     * @param string           $entity  entity FQCN (Fully-Qualified Class Name)
     * @param string           $field   field translated
     * @param multitype:string $locales locale string
     *
     * @return \Gedmo\Translatable\Entity\MappedSuperclass\AbstractTranslation
     */
    public function findTranslatedObjects($entity, $field, $locales = null)
    {
        $wrapped = new EntityWrapper($entity, $this->_em);
        if ($wrapped->hasValidIdentifier()) {
            $entityId = $wrapped->getIdentifier();
            $entityClass = $wrapped->getMetadata()->rootEntityName;

            $criterias = array('foreignKey' => $entityId, 'objectClass' => $entityClass, 'field' => $field);

            if ($locales !== null) {
                $criterias['locale'] = $locales;
            }

            return $this->findBy($criterias);
        }

        return array();
    }
}
