<?php

namespace Pim\Bundle\VersioningBundle\Doctrine\MongoDBODM\Saver;

use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ODM\MongoDB\DocumentManager;
use Pim\Bundle\TransformBundle\Normalizer\MongoDB\VersionNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Service to massively insert versions.
 * Useful for bulk saving of versionable objects.
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BulkVersionSaver implements BulkSaverInterface
{
    /** @var DocumentManager */
    protected $documentManager;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var string */
    protected $versionClass;

    /**
     * @param DocumentManager          $documentManager
     * @param NormalizerInterface      $normalizer
     * @param string                   $versionClass
     */
    public function __construct(
        DocumentManager $documentManager,
        NormalizerInterface $normalizer,
        $versionClass
    ) {
        $this->documentManager = $documentManager;
        $this->normalizer      = $normalizer;
        $this->versionClass    = $versionClass;
    }

    /**
     * {@inheritdoc}
     */
    public function saveAll(array $versions, array $options = [])
    {
        $normalizedVersions = [];
        foreach ($versions as $version) {
            $normalizedVersions[] = $this->normalizer->normalize($version, VersionNormalizer::FORMAT);
        }

        if (0 < count($normalizedVersions)) {
            $collection = $this->documentManager->getDocumentCollection($this->versionClass);
            $collection->batchInsert($normalizedVersions);
        }
    }
}
