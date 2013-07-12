<?php

namespace Pim\Bundle\ProductBundle\Entity\Repository;

use Pim\Bundle\ProductBundle\Doctrine\EntityRepository;

/**
 * Repository
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyRepository extends EntityRepository
{
    /**
     * @param $localeCode the locale to use for name translation
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function buildAllOrderedByLabel($localeCode)
    {
        $localeCode = 'default';
        $build = $this->build()
            ->addSelect('translation')
            ->leftJoin('family.translations', 'translation')
        /*
         * TODO
            ->addSelect('translation')
            ->leftJoin('family.translations', 'translation', 'with', 'translation.locale = :locale')
            ->setParameter('locale', $localeCode)
            ->orderBy('translation.label')
            ->addSelect('defaultTranslation')
            ->leftJoin('family.translations', 'defaultTranslation', 'with', 'defaultTranslation.locale = :defaultLocale')
            ->setParameter('defaultLocale', 'default')
            ->addOrderBy('defaultTranslation.label')*/
        ->addOrderBy('family.code')
        ;

        return $build;
    }

    /**
     * @param integer $id
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function buildOneWithAttributes($id)
    {
        return $this
            ->buildOne($id)
            ->addSelect('attribute')
            ->leftJoin('family.attributes', 'attribute')
            ->leftJoin('attribute.group', 'group')
            ->addOrderBy('group.sortOrder', 'ASC')
            ->addOrderBy('attribute.sortOrder', 'ASC');
    }
}
