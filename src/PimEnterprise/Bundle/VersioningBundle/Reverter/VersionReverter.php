<?php

namespace PimEnterprise\Bundle\VersioningBundle\Reverter;

use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\VersioningBundle\Model\Version;
use PimEnterprise\Bundle\VersioningBundle\Denormalizer\ProductDenormalizer;

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
    /** @var ProductDenormalizer */
    protected $denormalizer;

    /** @var ProductManager */
    protected $manager;

    /**
     * @param ProductManager      $manager
     * @param ProductDenormalizer $denormalizer
     */
    public function __construct(
        ProductManager $manager,
        ProductDenormalizer $denormalizer
    ) {
        $this->manager      = $manager;
        $this->denormalizer = $denormalizer;
    }

    /**
     * Revert an entity to a previous version
     *
     * @param Version $version
     */
    public function revert(Version $version)
    {
        $class = $version->getResourceName();
        $data  = $version->getSnapshot();

        $object = $this->denormalizer->denormalize($class, $data);

        if (null !== $object->getFamily()) {
            var_dump($object->getFamily()->getCode());
        } else {
            var_dump('NULL');
        }

        //$this->manager->saveProduct($object);
    }
}
