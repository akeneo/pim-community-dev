<?php
namespace Pim\Bundle\ProductBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Repository for AttributeGroup entity
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class AttributeGroupRepository extends EntityRepository
{

    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria, array $orderBy = array('sortOrder' => 'ASC'), $limit = null, $offset = null)
    {
        return parent::findBy($criteria, $orderBy, $limit, $offset);
    }
}
