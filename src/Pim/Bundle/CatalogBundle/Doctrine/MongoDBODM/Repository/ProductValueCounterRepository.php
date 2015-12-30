<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Repository;

use Doctrine\ODM\MongoDB\DocumentManager;
use Pim\Component\Catalog\Repository\ProductValueCounterRepositoryInterface;

/**
 * Product value repository used to retrieve the number of product values. This number can be used
 * to know whether MongoDB support should be enabled or not.
 *
 * @author    Remy Betus <remy.betus@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueCounterRepository implements ProductValueCounterRepositoryInterface
{
    /** @var DocumentManager */
    protected $documentManager;

    /** @var string */
    protected $productClass;

    /**
     * @param DocumentManager $documentManager
     * @param string          $productClass
     */
    public function __construct(DocumentManager $documentManager, $productClass)
    {
        $this->documentManager = $documentManager;
        $this->productClass = $productClass;
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        $qb = $this->documentManager->createQueryBuilder($this->productClass);
        $qb->distinct('values._id');

        return $qb->getQuery()->execute()->count();
    }
}
