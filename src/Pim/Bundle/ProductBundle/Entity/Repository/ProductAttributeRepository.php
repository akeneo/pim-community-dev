<?php
namespace Pim\Bundle\ProductBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\FlexibleEntityBundle\Entity\Repository\AttributeRepository;
use Pim\Bundle\ProductBundle\Entity\AttributeGroup;

/**
 * Repository for attribute entity
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAttributeRepository extends AttributeRepository
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
        $qb = $this->createQueryBuilder('a');

        if (!empty($attributes)) {
            $ids = array_map(
                function ($attribute) {
                    return $attribute->getId();
                },
                $attributes
            );

            $qb->where($qb->expr()->notIn('a.id', $ids));
        }
        $qb->orderBy('a.group');

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
}
