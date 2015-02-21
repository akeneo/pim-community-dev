<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogBundle\Doctrine\ORM;

use Doctrine\ORM\EntityManager;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\ProductMassActionRepository as BaseProductMassActionRepository;
use PimEnterprise\Bundle\WorkflowBundle\Doctrine\ORM\PublishedProductRepository;

/**
 * Overriden product mass action repository
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class ProductMassActionRepository extends BaseProductMassActionRepository
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
