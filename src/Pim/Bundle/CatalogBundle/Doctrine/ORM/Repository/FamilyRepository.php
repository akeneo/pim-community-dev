<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityRepository;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Repository\FamilyRepositoryInterface;

/**
 * Repository
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyRepository extends EntityRepository implements FamilyRepositoryInterface
{
    /**
     * {@inheritdoc}
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

        if (null !== $qb->getDQLPart('where')) {
            $whereParts = $qb->getDQLPart('where')->getParts();
            $qb->resetDQLPart('where');

            foreach ($whereParts as $part) {
                if (!is_string($part) || !strpos($part, 'entityIds')) {
                    $qb->andWhere($part);
                }
            }
        }

        $qb->setParameters(
            $qb->getParameters()->filter(
                function ($parameter) {
                    return $parameter->getName() !== 'entityIds';
                }
            )
        );

        // remove limit of the query
        $qb->setMaxResults(null);
    }

    /**
     * @param int $id
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function buildOneWithAttributes($id)
    {
        return $this
            ->createQueryBuilder('family')
            ->where('family.id = '.intval($id))
            ->addSelect('attribute')
            ->leftJoin('family.attributes', 'attribute')
            ->leftJoin('attribute.group', 'group')
            ->addOrderBy('group.sortOrder', 'ASC')
            ->addOrderBy('attribute.sortOrder', 'ASC');
    }

    /**
     * {@inheritdoc}
     */
    public function getFullRequirementsQB(FamilyInterface $family, $localeCode)
    {
        $qb = $this->getEntityManager()
            ->getRepository('Pim\Bundle\CatalogBundle\Entity\AttributeRequirement')
            ->createQueryBuilder('r')
            ->select('r, a, t')
            ->leftJoin('r.attribute', 'a');

        if (null !== $localeCode) {
            $qb->leftJoin('a.translations', 't', 'WITH', 't.locale = :localeCode')
                ->setParameter('localeCode', $localeCode);
        } else {
            $qb->leftJoin('a.translations', 't');
        }

        $qb->where('r.family = :family')
            ->setParameter('family', $family);

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function getFullFamilies(FamilyInterface $family = null, ChannelInterface $channel = null)
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
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function findAttributeIdsFromFamilies(array $familyIds)
    {
        $qb = $this->createQueryBuilder('f');
        $qb
            ->select('f.id AS f_id, a.id AS a_id')
            ->leftJoin('f.attributes', 'a')
            ->where($qb->expr()->in('f.id', $familyIds));

        $results = $qb->getQuery()->getArrayResult();
        $attrByFamilies = [];
        foreach ($results as $result) {
            $attrByFamilies[$result['f_id']][] = $result['a_id'];
        }

        return $attrByFamilies;
    }

    /**
     * {@inheritdoc}*
     */
    public function findByIds(array $familyIds)
    {
        if (empty($familyIds)) {
            throw new \InvalidArgumentException('Array must contain at least one family id');
        }

        $qb = $this->createQueryBuilder('f');
        $qb->where($qb->expr()->in('f.id', ':family_ids'));
        $qb->setParameter(':family_ids', $familyIds);

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($code)
    {
        return $this->findOneBy(['code' => $code]);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return ['code'];
    }

    /**
     * {@inheritdoc}
     */
    public function countAll()
    {
        $count = $this->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->getQuery()
            ->getSingleScalarResult();

        return $count;
    }

    /**
     * {@inheritdoc}
     */
    public function hasAttribute($id, $attributeCode)
    {
        $queryBuilder = $this->createQueryBuilder('f')
            ->leftJoin('f.attributes', 'a')
            ->where('f.id = :id')
            ->andWhere('a.code = :code')
            ->setMaxResults(1)
            ->setParameters([
                'id'   => $id,
                'code' => $attributeCode,
            ])
            ->addGroupBy('a.id');

        $result = $queryBuilder->getQuery()->getArrayResult();

        return count($result) > 0;
    }
}
