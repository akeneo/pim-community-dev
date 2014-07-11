<?php

namespace PimEnterprise\Bundle\CatalogBundle\Doctrine\MongoDBODM;

use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\ProductMassActionRepository as PimProductMassActionRepository;

/**
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProductMassActionRepository extends PimProductMassActionRepository
{
    /**
     * {@inheritdoc}
     */
    public function deleteFromIds(array $ids)
    {
        throw new \Exception('Not yet implemented');
    }
}
