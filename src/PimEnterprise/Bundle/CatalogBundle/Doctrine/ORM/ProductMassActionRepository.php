<?php

namespace PimEnterprise\Bundle\CatalogBundle\Doctrine\ORM;

use Doctrine\ORM\EntityManager;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\ProductMassActionRepository as PimProductMassActionRepository;
use PimEnterprise\Bundle\WorkflowBundle\Doctrine\ORM\PublishedProductRepository;

/**
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProductMassActionRepository extends PimProductMassActionRepository
{
    /** @var PublishedProductRepository */
    protected $publishedRepository;

    /**
     * @param EntityManager              $em
     * @param string                     $entityName
     * @param PublishedProductRepository $publishedRepository
     */
    public function __construct(
        EntityManager $em,
        $entityName,
        PublishedProductRepository $publishedRepository
    ) {
        parent::__construct($em, $entityName);

        $this->publishedRepository = $publishedRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteFromIds(array $ids)
    {
        $publishedIds = $this->publishedRepository->getProductIdsMapping($ids);
        if (!empty($publishedIds)) {
            throw new \Exception(
                'Impossible to mass delete products. You should not have any published products in your selection.'
            );
        }

        return parent::deleteFromIds($ids);
    }
}
