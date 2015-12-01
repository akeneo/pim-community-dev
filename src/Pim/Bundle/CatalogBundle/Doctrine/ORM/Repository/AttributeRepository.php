<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypes;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\CatalogBundle\Model\AttributeGroupInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;

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
    public function findAllWithTranslations()
    {
        $qb = $this->createQueryBuilder('a')
            ->addSelect('translation')
            ->leftJoin('a.translations', 'translation');

        return $qb->getQuery()->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function findWithGroups(array $attributeIds = array(), array $criterias = array())
    {
        $qb = $this->findWithGroupsQB($attributeIds, $criterias);

        return $qb->getQuery()->execute();
    }

    /**
     * Find attributes with related attribute groups QB
     *
     * @param array $attributeIds
     * @param array $criterias
     *
     * @return QueryBuilder
     */
    protected function findWithGroupsQB(array $attributeIds = array(), array $criterias = array())
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
    public function getChoices(array $options)
    {
        $qb = $this->getChoicesQB($options);
        $result = $qb->getQuery()->getArrayResult();

        // Build choices list
        $attributes = [];
        foreach ($result as $key => $attribute) {
            $attributes[$attribute['group_label']][$attribute['id']] = $attribute['attribute_label'];
            unset($result[$key]);
        }

        return $attributes;
    }

    /**
     * Create query builder for choices
     *
     * @param array $options
     *
     * @throws \InvalidArgumentException
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getChoicesQB(array $options)
    {
        if (!isset($options['excluded_attribute_ids'])) {
            throw new \InvalidArgumentException('Option "excluded_attribute_ids" is required');
        }

        if (!isset($options['locale_code'])) {
            throw new \InvalidArgumentException('Option "locale_code" is required');
        }

        $qb = $this->createQueryBuilder('a');
        $qb
            ->select('a.id')
            ->addSelect('COALESCE(at.label, CONCAT(\'[\', a.code, \']\')) as attribute_label')
            ->addSelect('COALESCE(gt.label, CONCAT(\'[\', g.code, \']\')) as group_label')
            ->leftJoin('a.translations', 'at', 'WITH', 'at.locale = :localeCode')
            ->leftJoin('a.group', 'g')
            ->leftJoin('g.translations', 'gt', 'WITH', 'gt.locale = :localeCode')
            ->orderBy('g.sortOrder, a.sortOrder')
            ->setParameter('localeCode', $options['locale_code']);

        if (!empty($options['excluded_attribute_ids'])) {
            $qb->andWhere(
                $qb->expr()->notIn('a.id', $options['excluded_attribute_ids'])
            );
        }

        return $qb;
    }

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
            ->andWhere('a.attributeType IN (:file_type, :image_type)')
            ->setParameters(
                array(
                    ':file_type'  => AttributeTypes::FILE,
                    ':image_type' => AttributeTypes::IMAGE,
                )
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
    public function findAllAxisQB()
    {
        $qb = $this->createQueryBuilder('a');
        $qb
            ->andWhere(
                $qb->expr()->in(
                    'a.attributeType',
                    [AttributeTypes::OPTION_SIMPLE_SELECT, 'pim_reference_data_simpleselect']
                )
            )
            ->andWhere($qb->expr()->neq('a.scopable', 1))
            ->andWhere($qb->expr()->neq('a.localizable', 1));

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function findAllAxis()
    {
        $qb = $this->findAllAxisQB();

        return $qb->getQuery()->getResult();
    }

    /**
     * Get available attributes as label
     *
     * @return AttributeInterface[]
     */
    protected function getAvailableAttributesAsLabel()
    {
        $qb = $this->createQueryBuilder('a');
        $qb
            ->andWhere(
                $qb->expr()->in('a.attributeType', array(AttributeTypes::TEXT, AttributeTypes::IDENTIFIER))
            );

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableAttributesAsLabelChoice()
    {
        $attributes = $this->getAvailableAttributesAsLabel();

        $choices = array();
        foreach ($attributes as $attribute) {
            $choices[$attribute->getId()] = $attribute->getLabel();
        }

        return $choices;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($code)
    {
        return $this->findOneBy(array('code' => $code));
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return array('code');
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
        $results = $qb->getQuery()->execute(array(), AbstractQuery::HYDRATE_ARRAY);

        if ($withLabel) {
            $labelExpr = 'COALESCE(trans.label, CONCAT(CONCAT(\'[\', att.code), \']\'))';
            $groupLabelExpr = 'COALESCE(gtrans.label, CONCAT(CONCAT(\'[\', g.code), \']\'))';

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
            $attributes = $qb->getQuery()->execute(array(), AbstractQuery::HYDRATE_ARRAY);
            foreach ($attributes as $data) {
                $results[$data['code']]['label']      = $data['label'];
                $results[$data['code']]['group']      = $data['groupLabel'];
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
        }

        if (is_array($groupIds) && !empty($groupIds)) {
            $qb->andWhere("att.group IN (:groupIds)");
            $qb->setParameter('groupIds', $groupIds);
        } elseif (is_array($groupIds)) {
            return [];
        }

        $result = $qb->getQuery()->execute([], AbstractQuery::HYDRATE_ARRAY);

        return array_keys($result);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeIds($codes)
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('att.id')
            ->from($this->_entityName, 'att', 'att.id')
            ->andWhere('att.code IN (:codes)');

        $parameters = ['codes' => $codes];
        $result = $qb->getQuery()->execute($parameters, AbstractQuery::HYDRATE_ARRAY);

        return array_keys($result);
    }

    /**
     * {@inheritdoc}
     */
    public function createDatagridQueryBuilder()
    {
        $qb = $this->createQueryBuilder('a');
        $rootAlias = $qb->getRootAlias();

        $labelExpr = sprintf(
            '(CASE WHEN translation.label IS NULL THEN %s.code ELSE translation.label END)',
            $rootAlias
        );
        $groupExpr = '(CASE WHEN gt.label IS NULL THEN attributeGroup.code ELSE gt.label END)';

        $qb
            ->addSelect($rootAlias)
            ->addSelect(sprintf("%s AS label", $labelExpr))
            ->addSelect(sprintf("%s AS groupLabel", $groupExpr))
            ->addSelect('translation.label')
            ->addSelect('attributeGroup.code');

        $qb
            ->leftJoin($rootAlias .'.translations', 'translation', 'WITH', 'translation.locale = :localeCode')
            ->leftJoin($rootAlias .'.group', 'attributeGroup')
            ->leftJoin('attributeGroup.translations', 'gt', 'WITH', 'gt.locale = :localeCode');

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return $this->findOneBy(array('attributeType' => AttributeTypes::IDENTIFIER));
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierCode()
    {
        if (null === $this->identifierCode) {
            $this->identifierCode = $this->getIdentifier()->getCode();
        }

        return $this->identifierCode;
    }

    /**
     * {@inheritdoc}
     */
    public function getNonIdentifierAttributes()
    {
        $qb = $this->createQueryBuilder('a');

        $qb
            ->andWhere($qb->expr()->neq('a.attributeType', '?1'))
            ->setParameter(1, AttributeTypes::IDENTIFIER);

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeTypeByCodes(array $codes)
    {
        $results = $this->createQueryBuilder('a')
            ->select('a.code, a.attributeType')
            ->where('a.code IN (:codes)')
            ->setParameter('codes', $codes)
            ->getQuery()
            ->getArrayResult();

        $attributes = [];
        if (!empty($results)) {
            foreach ($results as $attribute) {
                $attributes[$attribute['code']] = $attribute['attributeType'];
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
            ->where($qb->expr()->eq('a.attributeType', ':type'))
            ->setParameter(':type', $type);

        $result = $qb->getQuery()->getScalarResult();

        if (null === $result) {
            return [];
        } else {
            return array_map('current', $qb->getQuery()->getScalarResult());
        }
    }

    /**
     * Gets attribute codes by attribute group
     *
     * @param AttributeGroupInterface $group
     *
     * @return string[]
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
    public function countAll()
    {
        $count = $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->getQuery()
            ->getSingleScalarResult();

        return $count;
    }
}
