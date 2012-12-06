<?php
namespace Pim\Bundle\CatalogBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Pim\Bundle\CatalogBundle\Doctrine\ProductAttributeRepositoryInterface;
use Oro\Bundle\FlexibleEntityBundle\Model\EntitySet as AbstractEntitySet;

/**
 * Custom repository for product attribute
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductAttributeRepository extends DocumentRepository implements ProductAttributeRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findAllExcept(AbstractEntitySet $set)
    {
        // ids to exlude
        $excludeIds = array();
        foreach ($set->getGroups() as $group) {
            foreach ($group->getAttributes() as $attribute) {
                $excludeIds[]= $attribute->getId();
            }
        }
        // query
        $qb = $this->createQueryBuilder();
        $q = $qb->field('id')->notIn($excludeIds)->getQuery();

        return $q->execute();
    }
}
