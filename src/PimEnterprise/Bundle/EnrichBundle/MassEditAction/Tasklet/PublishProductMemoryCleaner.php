<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\MassEditAction\Tasklet;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\UnitOfWork;

/**
 * This class is a cleaner to use only during a mass publish. It cleans objects that are still in memory to avoid leaks.
 *
 * @author Tamara Robichet <tamara.robichet@akeneo.com>
 */
class PublishProductMemoryCleaner
{
    /** @var ManagerRegistry */
    protected $managerRegistry;

    /** @var string */
    protected $productClass;

    /** @var string */
    protected $entityVersionClass;

    /** @var string */
    protected $publishedProductValueClass;

    /** @var string */
    protected $publishedProductClass;

    /**
     * @param ManagerRegistry $registry
     * @param string          $productClass
     * @param string          $entityVersionClass
     * @param string          $publishedProductValueClass
     * @param string          $publishedProductClass
     */
    public function __construct(
        ManagerRegistry $registry,
        $productClass,
        $entityVersionClass,
        $publishedProductValueClass,
        $publishedProductClass
    ) {
        $this->managerRegistry            = $registry;
        $this->productClass               = $productClass;
        $this->entityVersionClass         = $entityVersionClass;
        $this->publishedProductValueClass = $publishedProductValueClass;
        $this->publishedProductClass      = $publishedProductClass;
    }

    /**
     * Cleans up data still in memory after detaching products
     */
    public function cleanupMemory()
    {
        $entityVersionManager = $this->managerRegistry->getManagerForClass($this->entityVersionClass);
        $entityVersionManager->clear($this->entityVersionClass);

        $publishProdValueManager = $this->managerRegistry->getManagerForClass($this->publishedProductValueClass);
        $publishProdValueManager->clear($this->publishedProductValueClass);

        $publishProductManager = $this->managerRegistry->getManagerForClass($this->publishedProductClass);
        $publishProductManager->clear($this->publishedProductClass);

        $productManager = $this->managerRegistry->getManagerForClass($this->productClass);
        $uow = $productManager->getUnitOfWork();
        $identityMapObjectIds = $uow->getIdentityMap();
        $objectIds = [];

        foreach ($identityMapObjectIds as $objects) {
            foreach ($objects as $entity) {
                $oid = spl_object_hash($entity);
                $objectIds[] = $oid;
            }
        }

        $originalDocumentData = &$this->getOriginalDocumentData($uow);
        foreach (array_diff(array_keys($originalDocumentData), $objectIds) as $id) {
            unset($originalDocumentData[$id]);
        }

        $parentAssociations = &$this->getParentAssociations($uow);
        foreach (array_diff(array_keys($parentAssociations), $objectIds) as $id) {
            unset($parentAssociations[$id]);
        }

        $embeddedDocumentsRegistry = &$this->getEmbeddedDocumentsRegistry($uow);
        foreach (array_diff(array_keys($embeddedDocumentsRegistry), $objectIds) as $id) {
            unset($embeddedDocumentsRegistry[$id]);
        }
    }

    /**
     * Get the private originalDocumentData from UoW
     *
     * @param UnitOfWork $uow
     *
     * @return array
     */
    private function &getOriginalDocumentData($uow)
    {
        $closure = \Closure::bind(function &($uow) {
            return $uow->originalDocumentData;
        }, null, $uow);

        return $closure($uow);
    }

    /**
     * Get the private parentAssociations from UoW
     *
     * @param UnitOfWork $uow
     *
     * @return array
     */
    private function &getParentAssociations($uow)
    {
        $closure = \Closure::bind(function &($uow) {
            return $uow->parentAssociations;
        }, null, $uow);

        return $closure($uow);
    }

    /**
     * Get the private parentAssociations from UoW
     *
     * @param UnitOfWork $uow
     *
     * @return array
     */
    private function &getEmbeddedDocumentsRegistry($uow)
    {
        $closure = \Closure::bind(function &($uow) {
            return $uow->embeddedDocumentsRegistry;
        }, null, $uow);

        return $closure($uow);
    }
}
