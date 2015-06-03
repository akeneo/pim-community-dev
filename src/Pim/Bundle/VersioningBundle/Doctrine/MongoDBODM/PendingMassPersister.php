<?php

namespace Pim\Bundle\VersioningBundle\Doctrine\MongoDBODM;

use Doctrine\ODM\MongoDB\DocumentManager;
use Pim\Bundle\TransformBundle\Normalizer\MongoDB\VersionNormalizer;
use Pim\Bundle\VersioningBundle\Builder\VersionBuilder;
use Pim\Bundle\VersioningBundle\Doctrine\AbstractPendingMassPersister;
use Pim\Bundle\VersioningBundle\Manager\VersionContext;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Service to massively insert pending versions.
 * Useful for massive imports of products.
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PendingMassPersister extends AbstractPendingMassPersister
{
    /** @var DocumentManager */
    protected $documentManager;

    /**
     * @param VersionBuilder      $versionBuilder
     * @param VersionManager      $versionManager
     * @param VersionContext      $versionContext
     * @param NormalizerInterface $normalizer
     * @param string              $versionClass
     * @param DocumentManager     $documentManager
     */
    public function __construct(
        VersionBuilder $versionBuilder,
        VersionManager $versionManager,
        VersionContext $versionContext,
        NormalizerInterface $normalizer,
        $versionClass,
        DocumentManager $documentManager
    ) {
        parent::__construct($versionBuilder, $versionManager, $normalizer, $versionContext, $versionClass);
        $this->documentManager = $documentManager;
    }

    /**
     * {@inheritDoc}
     */
    protected function batchInsertPendingVersions(array $pendingVersions)
    {
        $mongodbVersions = [];

        foreach ($pendingVersions as $pendingVersion) {
            $mongodbVersions[] = $this->normalizer->normalize($pendingVersion, VersionNormalizer::FORMAT);
        }

        $collection = $this->documentManager->getDocumentCollection($this->versionClass);
        $collection->batchInsert($mongodbVersions);
    }
}
