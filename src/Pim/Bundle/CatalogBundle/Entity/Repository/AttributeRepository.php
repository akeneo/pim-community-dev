<?php

namespace Pim\Bundle\CatalogBundle\Entity\Repository;

use Pim\Bundle\FlexibleEntityBundle\Entity\Repository\AttributeRepository as FlexibleAttributeRepository;
use Pim\Bundle\EnrichBundle\Form\DataTransformer\ChoicesProviderInterface;

/**
 * Repository for attribute entity
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeRepository extends FlexibleAttributeRepository implements
    ReferableEntityRepositoryInterface,
    ChoicesProviderInterface
{
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
     * {@inheritdoc}
     */
    public function getChoices(array $options)
    {
        if (!isset($options['excluded_attribute_ids'])) {
            throw new \InvalidArgumentException('Option "excluded_attribute_ids" is required');
        }

        if (!isset($options['localeCode'])) {
            throw new \InvalidArgumentException('Option "localeCode" is required');
        }

        $qb = $this
            ->createQueryBuilder('a')
            ->select('a.id')
            ->addSelect('COALESCE(at.label, CONCAT(\'[\', a.code, \']\')) as attribute_label')
            ->addSelect('COALESCE(gt.label, CONCAT(\'[\', g.code, \']\')) as group_label')
            ->leftJoin('a.translations', 'at', 'WITH', 'at.locale = :localeCode')
            ->leftJoin('a.group', 'g')
            ->leftJoin('g.translations', 'gt', 'WITH', 'gt.locale = :localeCode')
            ->orderBy('a.group')
            ->setParameter('localeCode', $options['localeCode']);

        if (!empty($options['excluded_attribute_ids'])) {
            $qb->andWhere(
                $qb->expr()->notIn('a.id', $options['excluded_attribute_ids'])
            );
        }

        $result = $qb->getQuery()->getArrayResult();

        $attributes = [];
        foreach ($result as $key => $attribute) {
            $attributes[$attribute['group_label']][$attribute['id']] = $attribute['attribute_label'];
            unset($result[$key]);
        }

        return $attributes;
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
}
