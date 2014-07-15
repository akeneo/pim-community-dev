<?php

namespace PimEnterprise\Bundle\CatalogBundle\Doctrine\MongoDBODM;

use Doctrine\ODM\MongoDB\DocumentManager;
use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\ProductMassActionRepository as PimProductMassActionRepository;
use Pim\Bundle\CatalogBundle\Entity\Repository\FamilyRepository;
use PimEnterprise\Bundle\WorkflowBundle\Doctrine\MongoDBODM\PublishedProductRepository;

/**
 * Overriden product mass action repository
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProductMassActionRepository extends PimProductMassActionRepository
{
    /** @var PublishedProductRepository */
    protected $publishedRepository;

    /**
     * @param DocumentManager            $dm
     * @param string                     $documentName
     * @param FamilyRepository           $familyRepository
     * @param PublishedProductRepository $publishedRepository
     */
    public function __construct(
        DocumentManager $dm,
        $documentName,
        FamilyRepository $familyRepository,
        PublishedProductRepository $publishedRepository
    ) {
        $this->dm = $dm;
        $this->documentName = $documentName;
        $this->familyRepository = $familyRepository;
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
