<?php

namespace PimEnterprise\Bundle\VersioningBundle\Reverter;

use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\VersioningBundle\Model\Version;
use PimEnterprise\Bundle\VersioningBundle\Denormalizer\ProductDenormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Version reverter that allow to revert an entity to a previous snapshot
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * @TODO: Make it works for all entities
 */
class VersionReverter
{
    /** @var SerializerInterface */
    protected $serializer;

    /** @var ManagerRegistry */
    protected $manager;

    /** @var ProductManager */
    protected $productManager;

    /**
     * @param ProductManager      $manager
     * @param SerializerInterface $serializer
     * @param ProductManager      $productManager
     */
    public function __construct(
        ManagerRegistry $manager,
        DenormalizerInterface $serializer,
        ProductManager $productManager
    ) {
        $this->manager        = $manager;
        $this->serializer     = $serializer;
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
        echo "ResourceId: ". $resourceId ."<br /><br />";

        $currentObject = $this->manager->getRepository($class)->find($resourceId);
        $revertedObject = $this->serializer->denormalize($data, $class, "csv", ['entity' => $currentObject]);

        $this->productManager->saveProduct($revertedObject);
    }
}
