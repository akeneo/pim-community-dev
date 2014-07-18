<?php

namespace PimEnterprise\Bundle\VersioningBundle\Reverter;

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

    /** @var ProductManager */
    protected $manager;

    /**
     * @param ProductManager      $manager
     * @param SerializerInterface $serializer
     */
    public function __construct(
        ProductManager $manager,
        DenormalizerInterface $serializer
    ) {
        $this->manager    = $manager;
        $this->serializer = $serializer;
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

//        $object = $this->manager->find($resourceId);
        $object = $this->serializer->denormalize($data, $class);

        if (null !== $object->getFamily()) {
            var_dump($object->getFamily()->getCode());
        } else {
            var_dump('NULL');
        }

        //$this->manager->saveProduct($object);
    }
}
