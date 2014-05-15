<?php

namespace Pim\Bundle\CatalogBundle\Entity\Repository;

use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Doctrine\ReferableEntityRepository;
use Pim\Bundle\EnrichBundle\Form\DataTransformer\ChoicesProviderInterface;

/**
 * Repository
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyRepository extends ReferableEntityRepository implements ChoicesProviderInterface
{
    /**
     * @param object  $qb
     * @param boolean $inset
     * @param mixed   $values
     *
     * @return null
     *
     * @TODO Move this code
     */
    public function applyMassActionParameters($qb, $inset, $values)
    {
        if ($values) {
            $rootAlias = $qb->getRootAlias();
                $valueWhereCondition =
                    $inset
                    ? $qb->expr()->in($rootAlias, $values)
                    : $qb->expr()->notIn($rootAlias, $values);
                $qb->andWhere($valueWhereCondition);
        }
        $whereParts = $qb->getDQLPart('where')->getParts();
        $qb->resetDQLPart('where');

        foreach ($whereParts as $part) {
            if (!is_string($part) || !strpos($part, 'entityIds')) {
                $qb->andWhere($part);
            }
        }

        $qb->setParameters(
            $qb->getParameters()->filter(
                function ($parameter) {
                    return $parameter->getName() !== 'entityIds';
                }
            )
        );

        // Allows hydration as object.
        // Family mass edit operation receives an array instead of a Family object
        $qb->select($qb->getRootAlias());
    }

    /**
     * {@inheritdoc}
     */
    public function getChoices(array $options)
    {
        if (!isset($options['localeCode'])) {
            throw new \InvalidArgumentException('Option "localeCode" is required');
        }

        $qb = $this->_em->createQueryBuilder()
            ->select('f.id')
            ->addSelect('COALESCE(ft.label, CONCAT(\'[\', f.code, \']\')) as label')
            ->from('Pim\Bundle\CatalogBundle\Entity\Family', 'f')
            ->leftJoin('f.translations', 'ft', 'WITH', 'ft.locale = :localeCode')
            ->orderBy('label')
            ->setParameter('localeCode', $options['localeCode']);

        $result  = $qb->getQuery()->getArrayResult();
        $choices = [];

        foreach ($result as $key => $family) {
            $choices[$family['id']] = $family['label'];
            unset($result[$key]);
        }

        return $choices;
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
     * Returns all families code with their required attributes code
     * Requirements can be restricted to a channel.
     *
     * @param Channel $channel
     *
     * return array
     */
    public function getFullFamilies(Family $family = null, Channel $channel = null)
    {
        $qb = $this->createQueryBuilder('f')
            ->select('f, c, l, r, a, cu')
            ->join('f.requirements', 'r')
            ->join('r.attribute', 'a')
            ->join('r.channel', 'c')
            ->join('c.locales', 'l')
            ->join('c.currencies', 'cu')
            ->where('r.required = 1');

        if (null !== $channel) {
            $qb->andWhere('r.channel = :channel')
                ->setParameter('channel', $channel);
        }

        if (null !== $family) {
            $qb->andWhere('f.id = :familyId')
                ->setParameter('familyId', $family->getId());
        }

        return $qb->getQuery()->getResult();
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

    /**
     * Find attribute ids from family ids
     *
     * @param array $familyIds
     *
     * @return array '<f_id>' => array(<attribute ids>)
     */
    public function findAttributeIdsFromFamilies(array $familyIds)
    {
        $qb = $this->createQueryBuilder('f');
        $qb
            ->select('f.id AS f_id, a.id AS a_id')
            ->leftJoin('f.attributes', 'a')
            ->where($qb->expr()->in('f.id', $familyIds));

        $results = $qb->getQuery()->getArrayResult();
        $attrByFamilies = array();
        foreach ($results as $result) {
            $attrByFamilies[$result['f_id']][] = $result['a_id'];
        }

        return $attrByFamilies;
    }
}
