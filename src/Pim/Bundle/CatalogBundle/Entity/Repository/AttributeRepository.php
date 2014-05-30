<?php

namespace Pim\Bundle\CatalogBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\AbstractQuery;
use Pim\Bundle\EnrichBundle\Form\DataTransformer\ChoicesProviderInterface;
use Pim\Bundle\CatalogBundle\Repository\ReferableEntityRepositoryInterface;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;

/**
 * Repository for attribute entity
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeRepository extends EntityRepository implements
    ReferableEntityRepositoryInterface,
    ChoicesProviderInterface
{
    /**
     * @var string $identifierCode
     */
    protected $identifierCode;

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function findAllWithTranslations()
    {
        $qb = $this->createQueryBuilder('a')
            ->addSelect('translation')
            ->leftJoin('a.translations', 'translation');

        return $qb->getQuery()->execute();
    }

    /**
     * Find attributes with related attribute groups
     *
     * @param array $attributeIds
     * @param array $criterias
     *
     * @return array
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

        // Move default group to the end
        if (isset($attributes[$options['default_group_label']])) {
            $default = $attributes[$options['default_group_label']];
            unset($attributes[$options['default_group_label']]);
            $attributes[$options['default_group_label']] = $default;
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

        if (!isset($options['default_group_label'])) {
            throw new \InvalidArgumentException('Option "default_group_label" is required');
        }

        $qb = $this->createQueryBuilder('a');
        $qb
            ->select('a.id')
            ->addSelect('COALESCE(at.label, CONCAT(\'[\', a.code, \']\')) as attribute_label')
            ->addSelect('COALESCE(gt.label, CONCAT(\'[\', g.code, \']\'), :defaultGroupLabel) as group_label')
            ->leftJoin('a.translations', 'at', 'WITH', 'at.locale = :localeCode')
            ->leftJoin('a.group', 'g')
            ->leftJoin('g.translations', 'gt', 'WITH', 'gt.locale = :localeCode')
            ->orderBy('g.sortOrder, a.sortOrder')
            ->setParameter('localeCode', $options['locale_code'])
            ->setParameter('defaultGroupLabel', $options['default_group_label']);

        if (!empty($options['excluded_attribute_ids'])) {
            $qb->andWhere(
                $qb->expr()->notIn('a.id', $options['excluded_attribute_ids'])
            );
        }

        return $qb;
    }

    /**
     * Find all attributes that belong to a group
     *
     * @return array
     */
    public function findAllGrouped()
    {
        $qb = $this->createQueryBuilder('a');
        $qb->where($qb->expr()->isNotNull('a.group'))->orderBy('a.code');

        return $qb->getQuery()->getResult();
    }

    /**
     * Find all unique attribute codes
     *
     * @return string[]
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
     * Find media attribute codes
     *
     * @return string[]
     */
    public function findMediaAttributeCodes()
    {
        $codes = $this
            ->createQueryBuilder('a')
            ->select('a.code')
            ->andWhere('a.attributeType IN (:file_type, :image_type)')
            ->setParameters(
                array(
                    ':file_type'  => 'pim_catalog_file',
                    ':image_type' => 'pim_catalog_image',
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
     * Find all attributes of type axis
     * An axis define a variation of a variant group
     * Axes are attributes with simple select option, not localizable and not scopable
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function findAllAxisQB()
    {
        $qb = $this->createQueryBuilder('a');
        $qb
            ->andWhere(
                $qb->expr()->in('a.attributeType', array('pim_catalog_simpleselect'))
            )
            ->andWhere($qb->expr()->neq('a.scopable', 1))
            ->andWhere($qb->expr()->neq('a.localizable', 1));

        return $qb;
    }

    /**
     * Find all axis
     *
     * @see findAllAxisQB
     *
     * @return array
     */
    public function findAllAxis()
    {
        $qb = $this->findAllAxisQB();

        return $qb->getQuery()->getResult();
    }

    /**
     * Get available attributes as label
     *
     * @return array
     */
    protected function getAvailableAttributesAsLabel()
    {
        $qb = $this->createQueryBuilder('a');
        $qb
            ->andWhere(
                $qb->expr()->in('a.attributeType', array('pim_catalog_text', 'pim_catalog_identifier'))
            );

        return $qb->getQuery()->getResult();
    }

    /**
     * Get available attributes as label as a choice
     *
     * @return array
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
    public function findByReference($code)
    {
        return $this->findOneBy(array('code' => $code));
    }

    /**
     * {@inheritdoc}
     */
    public function getReferenceProperties()
    {
        return array('code');
    }

    /**
     * Get attribute as array indexed by code
     *
     * @param boolean $withLabel translated label should be joined
     * @param string  $locale    the locale code of the label
     * @param array   $ids       the attribute ids
     *
     * @return array
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
            $groupLabelExpr = sprintf(
                'CASE WHEN g IS NOT NULL THEN COALESCE(gtrans.label, CONCAT(CONCAT(\'[\', g.code), \']\')) ELSE \'%s\'',
                AttributeGroup::DEFAULT_GROUP_CODE
            );

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
            $labels = $qb->getQuery()->execute(array(), AbstractQuery::HYDRATE_ARRAY);
            foreach ($labels as $data) {
                $results[$data['code']]['label']      = $data['label'];
                $results[$data['code']]['group']      = $data['groupLabel'];
                $results[$data['code']]['groupOrder'] = $data['sortOrder'];
            }
        }

        return $results;
    }

    /**
     * Get ids of attributes useable in grid
     *
     * @param array $codes
     * @param array $groupIds
     *
     * @return array
     */
    public function getAttributeIdsUseableInGrid($codes = null, $groupIds = null)
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('att.id')
            ->from($this->_entityName, 'att', 'att.id');

        $qb->andWhere("att.useableAsGridColumn = 1 OR att.useableAsGridFilter = 1");

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
     * Get ids from codes
     *
     * @param mixed $codes the attribute codes
     *
     * @return array
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
     * @return QueryBuilder
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
     * Get the identifier attribute
     * Only one identifier attribute can exists
     *
     * @return AbstractAttribute
     */
    public function getIdentifier()
    {
        return $this->findOneBy(array('attributeType' => 'pim_catalog_identifier'));
    }

    /**
     * Get the identifier code
     *
     * @return string
     */
    public function getIdentifierCode()
    {
        if (null === $this->identifierCode) {
            $this->identifierCode = $this->getIdentifier()->getCode();
        }

        return $this->identifierCode;
    }

    /**
     * Get non identifier attributes
     *
     * @return Attribute[]
     */
    public function getNonIdentifierAttributes()
    {
        $qb = $this->createQueryBuilder('a');

        $qb
            ->andWhere($qb->expr()->neq('a.attributeType', '?1'))
            ->setParameter(1, 'pim_catalog_identifier');

        return $qb->getQuery()->getResult();
    }
}
