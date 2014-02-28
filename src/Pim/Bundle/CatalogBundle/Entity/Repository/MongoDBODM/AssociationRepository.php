<?php

namespace Pim\Bundle\CatalogBundle\Entity\Repository\MongoDBODM;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Pim\Bundle\CatalogBundle\Entity\AssociationType;
use Pim\Bundle\CatalogBundle\Entity\Repository\ReferableEntityRepositoryInterface;
use Pim\Bundle\CatalogBundle\Model\AssociationRepositoryInterface;

/**
 * Association repository
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationRepository extends DocumentRepository implements AssociationRepositoryInterface,
 ReferableEntityRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function countForAssociationType(AssociationType $associationType)
    {
        throw new \Exception('Not yet implemented');
    }

    /**
     * {@inheritdoc}
     */
    public function findByReference($code)
    {
        throw new \Exception('Not yet implemented');
    }

    /**
     * {@inheritdoc}
     */
    public function getReferenceProperties()
    {
        return array('owner', 'associationType')
    }
}
