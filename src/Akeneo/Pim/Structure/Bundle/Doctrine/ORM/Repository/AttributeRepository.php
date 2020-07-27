<?php

namespace Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeGroup;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

/**
 * Repository for attribute entity
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeRepository extends EntityRepository implements
    IdentifiableObjectRepositoryInterface,
    AttributeRepositoryInterface
{
    /** @var string $identifierCode */
    protected $identifierCode;

    /**
     * {@inheritdoc}
     */
    public function findAllInDefaultGroup()
    {
        $qb = $this->createQueryBuilder('a');
        $qb
            ->innerJoin('a.group', 'g')
            ->where('g.code != :default_code')
            ->orderBy('a.code')
            ->setParameter(':default_code', AttributeGroup::DEFAULT_GROUP_CODE);

        return $qb->getQuery()->getResult();
    }

    /**
     * Find attributes with related attribute groups QB
     *
     * @param array $attributeIds
     * @param array $criterias
     *
     * @return QueryBuilder
     */
    protected function findWithGroupsQB(array $attributeIds = [], array $criterias = [])
    {
        $qb = $this->createQueryBuilder('a');
        $qb
            ->addSelect('atrans', 'g', 'gtrans')
            ->leftJoin('a.translations', 'atrans')
            ->leftJoin('a.group', 'g')
            ->leftJoin('g.translations', 'gtrans');

        if (!empty($attributeIds)) {
            $qb->andWhere($qb->expr()->in('a.id', $attributeIds));
        }

        if (isset($criterias['conditions'])) {
            foreach ($criterias['conditions'] as $criteria => $value) {
                $qb->andWhere($qb->expr()->eq(sprintf('a.%s', $criteria), $value));
            }
        }

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function findUniqueAttributeCodes()
    {
        $codes = $this
            ->createQueryBuilder('a')
            ->select('a.code')
            ->andWhere('a.unique = ?1')
            ->setParameter(1, true)
            ->getQuery()
            ->getArrayResult();

        return array_map(
            function ($data) {
                return $data['code'];
            },
            $codes
        );
    }

    /**
     * {@inheritdoc}
     */
    public function findMediaAttributeCodes()
    {
        $codes = $this
            ->createQueryBuilder('a')
            ->select('a.code')
            ->andWhere('a.type IN (:file_type, :image_type)')
            ->setParameters(
                [
                    ':file_type'  => AttributeTypes::FILE,
                    ':image_type' => AttributeTypes::IMAGE,
                ]
            )
            ->getQuery()
            ->getArrayResult();

        return array_map(
            function ($data) {
                return $data['code'];
            },
            $codes
        );
    }

    /**
     * {@inheritdoc}
     */
    public function findAllAxesQB()
    {
        $qb = $this->createQueryBuilder('a');
        $qb
            ->andWhere(
                $qb->expr()->in(
                    'a.type',
                    [AttributeTypes::OPTION_SIMPLE_SELECT, AttributeTypes::REFERENCE_DATA_SIMPLE_SELECT]
                )
            )
            ->andWhere($qb->expr()->neq('a.scopable', 1))
            ->andWhere($qb->expr()->neq('a.localizable', 1));

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function findAvailableAxes($locale)
    {
        $query = $this->getAxesQuery($locale);

        $axis = [];
        foreach ($query->getArrayResult() as $code) {
            $axis[$code['label']] = $code['id'];
        }

        return $axis;
    }

    /**
     * {@inheritdoc}
     */
    public function getAxesQuery($locale)
    {
        return $this->findAllAxesQB()
            ->select('a.id')
            ->addSelect('COALESCE(NULLIF(t.label, \'\'), CONCAT(\'[\', a.code, \']\')) as label')
            ->addSelect('a.code')
            ->leftJoin('a.translations', 't')
            ->andWhere('t.locale = :locale')
            ->setParameter('locale', $locale)
            ->orderBy('t.label')
            ->getQuery();
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
    public function getAttributesAsArray($withLabel = false, $locale = null, array $ids = [])
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('att')
            ->from($this->_entityName, 'att', 'att.code');
        if (!empty($ids)) {
            $qb->andWhere('att.id IN (:ids)')->setParameter('ids', $ids);
        }
        $results = $qb->getQuery()->execute([], AbstractQuery::HYDRATE_ARRAY);

        if ($withLabel) {
            $labelExpr = 'COALESCE(NULLIF(trans.label, \'\'), CONCAT(CONCAT(\'[\', att.code), \']\'))';
            $groupLabelExpr = 'COALESCE(NULLIF(gtrans.label, \'\'), CONCAT(CONCAT(\'[\', g.code), \']\'))';

            $qb = $this->_em->createQueryBuilder()
                ->select('att.code', sprintf('%s as label', $labelExpr))
                ->from($this->_entityName, 'att')
                ->leftJoin('att.translations', 'trans', 'WITH', 'trans.locale = :locale')
                ->leftJoin('att.group', 'g')
                ->leftJoin('g.translations', 'gtrans', 'WITH', 'gtrans.locale = :locale')
                ->addSelect('g.sortOrder')
                ->addSelect(sprintf('%s as groupLabel', $groupLabelExpr))
                ->setParameter('locale', $locale);
            if (!empty($ids)) {
                $qb->andWhere('att.id IN (:ids)')->setParameter('ids', $ids);
            }
            $attributes = $qb->getQuery()->execute([], AbstractQuery::HYDRATE_ARRAY);
            foreach ($attributes as $data) {
                $results[$data['code']]['label'] = $data['label'];
                $results[$data['code']]['group'] = $data['groupLabel'];
                $results[$data['code']]['groupOrder'] = $data['sortOrder'];
            }
        }

        return $results;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeIdsUseableInGrid($codes = null, $groupIds = null)
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('att.id')
            ->from($this->_entityName, 'att', 'att.id');

        if (is_array($codes) && !empty($codes)) {
            $qb->andWhere("att.code IN (:codes)");
            $qb->setParameter('codes', $codes);
        } elseif (is_array($codes)) {
            return [];
        }

        if (is_array($groupIds) && !empty($groupIds)) {
            $qb->andWhere("att.group IN (:groupIds)");
            $qb->setParameter('groupIds', $groupIds);
        } elseif (is_array($groupIds)) {
            return [];
        }

        $qb->andWhere('att.useableAsGridFilter = :useableInGrid');
        $qb->setParameter('useableInGrid', 1);

        $result = $qb->getQuery()->execute([], AbstractQuery::HYDRATE_ARRAY);

        return array_keys($result);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return $this->findOneBy(['type' => AttributeTypes::IDENTIFIER]);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierCode()
    {
        if (null === $this->identifierCode) {
            $code = $this->createQueryBuilder('a')
                ->select('a.code')
                ->andWhere('a.type = :type')
                ->setParameter('type', AttributeTypes::IDENTIFIER)
                ->setMaxResults(1)
                ->getQuery()->getSingleResult(Query::HYDRATE_SINGLE_SCALAR);

            $this->identifierCode = $code;
        }

        return $this->identifierCode;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeTypeByCodes(array $codes)
    {
        $results = $this->createQueryBuilder('a')
            ->select('a.code, a.type')
            ->where('a.code IN (:codes)')
            ->setParameter('codes', $codes)
            ->getQuery()
            ->getArrayResult();

        $attributes = [];
        if (!empty($results)) {
            foreach ($results as $attribute) {
                $attributes[$attribute['code']] = $attribute['type'];
            }
        }

        return $attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeCodesByType($type)
    {
        $qb = $this->createQueryBuilder('a');
        $qb
            ->select('a.code')
            ->where($qb->expr()->eq('a.type', ':type'))
            ->setParameter(':type', $type);

        $result = $qb->getQuery()->getScalarResult();

        if (null === $result) {
            return [];
        } else {
            return array_map('current', $qb->getQuery()->getScalarResult());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeCodesByGroup(AttributeGroupInterface $group)
    {
        $qb = $this->createQueryBuilder('a');
        $qb
            ->select('a.code')
            ->where($qb->expr()->eq('a.group', ':group'))
            ->setParameter(':group', $group);

        $result = $qb->getQuery()->getScalarResult();

        if (null === $result) {
            return [];
        }

        return array_map('current', $qb->getQuery()->getScalarResult());
    }

    /**
     * {@inheritdoc}
     */
    public function findAttributesByFamily(FamilyInterface $family)
    {
        $qb = $this->createQueryBuilder('a');
        $qb
            ->select('a, g')
            ->join('a.group', 'g')
            ->innerJoin('a.families', 'f', 'WITH', 'f.id = :family')
            ->setParameter(':family', $family->getId());

        return $qb->getQuery()->getResult();
    }
}
