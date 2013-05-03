<?php
namespace Pim\Bundle\ProductBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Pim\Bundle\ProductBundle\Entity\AttributeGroup;

/**
 * Repository for AttributeGroup entity
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductAttributeRepository extends EntityRepository
{
    public function findAllExcept(array $attributes)
    {
        $ids = array_map(function ($attribute) {
            return $attribute->getId();
        }, $attributes);

        $qb = $this->createQueryBuilder('a');
        $qb
            ->where($qb->expr()->notIn('a.id', $ids))
            ->orderBy('a.group')
        ;

        return $qb->getQuery()->getResult();
    }
}
