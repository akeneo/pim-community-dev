<?php

namespace Pim\Bundle\CatalogBundle\Entity\Repository;

use Pim\Bundle\FlexibleEntityBundle\Entity\Repository\AttributeRepository;

/**
 * Repository for attribute entity
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAttributeRepository extends AttributeRepository implements WithUniqueCodeRepositoryInterface
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
     * Get the query builder to find all product attributes except the ones
     * defined in arguments
     *
     * @param array $attributes The attributes to exclude from the results set
     *
     * @return Doctrine\ORM\QueryBuilder
     */
    public function getFindAllExceptQB(array $attributes)
    {
        $qb = $this->createQueryBuilder('a')->orderBy('a.group');

        if (!empty($attributes)) {
            $ids = array_map(
                function ($attribute) {
                    return $attribute->getId();
                },
                $attributes
            );

            $qb->andWhere(
                $qb->expr()->notIn('a.id', $ids)
            );
        }

        return $qb;
    }

    /**
     * Find all product attributes that belong to a group
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
     * Find all unique product attribute codes
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
            ->andWhere($qb->expr()->neq('a.translatable', 1));

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
    public function findByUniqueCode($code)
    {
        return $this->findOneBy(array('code' => $code));
    }

    /**
     * {@inheritdoc}
     */
    public function findByDataUniqueCode(array $data)
    {
        return $this->findByUniqueCode($data['unique_code']);
    }

    /**
     * {@inheritdoc}
     */
    public function getUniqueCodeProperties()
    {
        return array('code');
    }
}
