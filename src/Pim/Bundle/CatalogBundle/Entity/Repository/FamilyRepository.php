<?php

namespace Pim\Bundle\CatalogBundle\Entity\Repository;

use Pim\Bundle\CatalogBundle\Entity\Family;

/**
 * Repository
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyRepository extends ReferableEntityRepository
{
    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function buildAllWithTranslations()
    {
        return $this->build()->addSelect('translation')->leftJoin('family.translations', 'translation');
    }

    /**
     * Find all families ordered by label with fallback to default mecanism
     *
     * @return array
     */
    public function getIdToLabelOrderedByLabel()
    {
        $families = $this->buildAllWithTranslations()->getQuery()->execute();
        $orderedFamilies = [];
        foreach ($families as $family) {
            $orderedFamilies[$family->getId()] = $family->getLabel();
        }
        uasort(
            $orderedFamilies,
            function ($first, $second) {
                return ($first === $second) ? 0 : strcasecmp($first, $second);
            }
        );

        return $orderedFamilies;
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

    /**
     * Returns a querybuilder to get full requirements
     *
     * @param Family $family
     * @param type   $localeCode
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getFullRequirementsQB(Family $family, $localeCode)
    {
        return $this->getEntityManager()
            ->getRepository('Pim\Bundle\CatalogBundle\Entity\AttributeRequirement')
            ->createQueryBuilder('r')
            ->select('r, a, t')
            ->leftJoin('r.attribute', 'a')
            ->leftJoin('a.translations', 't', 'WITH', 't.locale=:localeCode')
            ->where('r.family=:family')
            ->setParameter('family', $family)
            ->setParameter('localeCode', $localeCode);
    }

    /**
     * @return QueryBuilder
     */
    public function createDatagridQueryBuilder()
    {
        $qb = $this->createQueryBuilder('f');
        $rootAlias = $qb->getRootAlias();

        $labelExpr = sprintf(
            '(CASE WHEN translation.label IS NULL THEN %s.code ELSE translation.label END)',
            $rootAlias
        );

        $qb
            ->addSelect($rootAlias)
            ->addSelect(sprintf('%s AS familyLabel', $labelExpr))
            ->addSelect('translation.label');

        $qb
            ->leftJoin($rootAlias . '.translations', 'translation', 'WITH', 'translation.locale = :localeCode')
            ->leftJoin('f.attributeAsLabel', 'a');

        return $qb;
    }
}
