<?php

namespace Akeneo\Pim\Structure\Component\Normalizer\InternalApi;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Platform\Bundle\UIBundle\Provider\StructureVersion\StructureVersionProviderInterface;
use Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionManager;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalizing data related to versioning is very costly. As we don't always need it, it's isolated in
 * this normalizer that decorates the main one.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VersionedAttributeNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /** @var array $supportedFormats */
    protected $supportedFormats = ['internal_api'];

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var VersionManager */
    protected $versionManager;

    /** @var NormalizerInterface */
    protected $versionNormalizer;

    /** @var StructureVersionProviderInterface */
    protected $structureVersionProvider;

    /**
     * @param NormalizerInterface               $normalizer
     * @param VersionManager                    $versionManager
     * @param NormalizerInterface               $versionNormalizer
     * @param StructureVersionProviderInterface $structureVersionProvider
     */
    public function __construct(
        NormalizerInterface $normalizer,
        VersionManager $versionManager,
        NormalizerInterface $versionNormalizer,
        StructureVersionProviderInterface $structureVersionProvider
    ) {
        $this->normalizer = $normalizer;
        $this->versionManager = $versionManager;
        $this->versionNormalizer = $versionNormalizer;
        $this->structureVersionProvider = $structureVersionProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($attribute, $format = null, array $context = [])
    {
        $normalizedAttribute = $this->normalizer->normalize($attribute, 'internal_api', $context);

        $firstVersion = $this->versionManager->getOldestLogEntry($attribute);
        $lastVersion = $this->versionManager->getNewestLogEntry($attribute);

        $firstVersion = null !== $firstVersion ?
            $this->versionNormalizer->normalize($firstVersion, 'internal_api', $context) :
            null;
        $lastVersion = null !== $lastVersion ?
            $this->versionNormalizer->normalize($lastVersion, 'internal_api', $context) :
            null;

        $normalizedAttribute['meta']['created'] = $firstVersion;
        $normalizedAttribute['meta']['updated'] = $lastVersion;
        $normalizedAttribute['meta']['structure_version'] = $this->structureVersionProvider->getStructureVersion();
        $normalizedAttribute['meta']['model_type'] = 'attribute';

        return $normalizedAttribute;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof AttributeInterface && in_array($format, $this->supportedFormats);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
