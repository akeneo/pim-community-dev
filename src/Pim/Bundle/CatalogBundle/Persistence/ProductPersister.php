<?php

namespace Pim\Bundle\CatalogBundle\Persistence;

use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\CatalogBundle\Manager\CompletenessManager;
use Pim\Bundle\CatalogBundle\Manager\ResourceManagerInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;

/**
 * Synchronize product with the database
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductPersister implements ResourceManagerInterface
{
    /** @var ManagerRegistry */
    protected $registry;

    /** @var CompletenessManager */
    protected $completenessManager;

    /** @var VersionManager */
    protected $versionManager;

    /**
     * @param ManagerRegistry     $registry
     * @param CompletenessManager $completenessManager
     * @param VersionManager      $versionManager
     */
    public function __construct(
        ManagerRegistry $registry,
        CompletenessManager $completenessManager,
        VersionManager $versionManager
    ) {
        $this->registry            = $registry;
        $this->completenessManager = $completenessManager;
        $this->versionManager      = $versionManager;
    }

    /**
     * {@inheritdoc}
     */
    public function save($object, array $options = [])
    {
        $options = array_merge(
            [
                'recalculate' => true,
                'flush' => true,
                'schedule' => true,
                'versioning' => true
            ],
            $options
        );

        $manager = $this->registry->getManagerForClass(get_class($object));
        $manager->persist($object);

        if ($options['schedule'] || $options['recalculate']) {
            $this->completenessManager->schedule($object);
        }

        if ($options['recalculate'] || $options['flush']) {
            $manager->flush();
        }

        if ($options['versioning']) {
            $changeset = [];
            $versions = $this->versionManager->buildVersions($object, $changeset);
            foreach ($versions as $version) {
                if ($version->getChangeset()) {
                    $manager->persist($version);
                }
            }
            if ($options['flush']) {
                $manager->flush();
            }
        }

        if ($options['recalculate']) {
            $this->completenessManager->generateMissingForProduct($object);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function saveAll(array $objects, array $options = [])
    {
        if (0 === count($objects)) {
            return;
        }
        // TODO : to fix
        $manager = $this->registry->getManagerForClass(get_class($objects[0]));

        $versions = [];
        $itemOptions = $options;
        $itemOptions['flush'] = false;
        $itemOptions['versioning'] = false;

        foreach ($objects as $object) {
            $this->save($object, $itemOptions);
        }

        if ($options['flush']) {
            $manager->flush();
        }

        if ($options['versioning']) {
            foreach ($objects as $object) {
                $changeset = [];
                $versions = $this->versionManager->buildVersions($object, $changeset);
                foreach ($versions as $version) {
                    if ($version->getChangeset()) {
                        $manager->persist($version);
                    }
                }
            }
            if ($options['flush']) {
                $manager->flush();
            }
        }
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated will be removed in 1.4
     */
    public function persist(ProductInterface $product, array $options)
    {
        $this->save($product, $options);
    }
}
