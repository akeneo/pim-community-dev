<?php

namespace PimEnterprise\Bundle\VersioningBundle\Reverter;

use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\VersioningBundle\Model\Version;
use PimEnterprise\Bundle\VersioningBundle\Denormalizer\ProductDenormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Product version reverter that allow to revert a product to a previous snapshot
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProductReverter
{
    /** @var DenormalizerInterface */
    protected $denormalizer;

    /** @var ManagerRegistry */
    protected $registry;

    /** @var ProductManager */
    protected $productManager;

    /**
     * @param ManagerRegistry     $registry
     * @param SerializerInterface $serializer
     * @param ProductManager      $productManager
     */
    public function __construct(
        ManagerRegistry $registry,
        DenormalizerInterface $denormalizer,
        ProductManager $productManager
    ) {
        $this->registry       = $registry;
        $this->denormalizer   = $denormalizer;
        $this->productManager = $productManager;
    }

    /**
     * Revert an entity to a previous version
     *
     * @param Version $version
     */
    public function revert(Version $version)
    {
        $class      = $version->getResourceName();
        $data       = $version->getSnapshot();
        $resourceId = $version->getResourceId();

        $currentObject = $this->registry->getRepository($class)->find($resourceId);
        $revertedObject = $this->denormalizer->denormalize($data, $class, "csv", ['entity' => $currentObject]);

        $this->productManager->saveProduct($revertedObject);
    }
}
