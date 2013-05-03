<?php
namespace Pim\Bundle\ProductBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Pim\Bundle\ProductBundle\Entity\AttributeGroup;

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
    public function findBy(array $criteria = array(), array $orderBy = array('sortOrder' => 'ASC'), $limit = null, $offset = null)
    {
        return parent::findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * Find all groups and add a virtual group for unpositioned attributes
     *
     * @return \ArrayAccess
     */
    public function findAllWithVirtualGroup()
    {
        $groups = $this->findBy();
        $virtual = new AttributeGroup();
        $virtual->setName('Others')->setId(0);
        $groups[] = $virtual;

        return $groups;
    }
}
