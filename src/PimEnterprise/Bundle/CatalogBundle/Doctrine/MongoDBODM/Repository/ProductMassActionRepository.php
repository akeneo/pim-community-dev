<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogBundle\Doctrine\MongoDBODM\Repository;

use Doctrine\ODM\MongoDB\DocumentManager;
use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Repository\ProductMassActionRepository as BaseMassActionRepository;
use Pim\Bundle\CatalogBundle\Repository\FamilyRepositoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Doctrine\MongoDBODM\Repository\PublishedProductRepository;

/**
 * Overriden product mass action repository
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class ProductMassActionRepository extends BaseMassActionRepository
{
    /** @var PublishedProductRepository */
    protected $publishedRepository;

    /**
     * @param DocumentManager            $dm
     * @param string                     $documentName
     * @param FamilyRepositoryInterface  $familyRepository
     * @param PublishedProductRepository $publishedRepository
     */
    public function __construct(
        DocumentManager $dm,
        $documentName,
        FamilyRepositoryInterface $familyRepository,
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
