<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Platform\Bundle\UIBundle\Provider\StructureVersion\StructureVersionProviderInterface;
use Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionManager;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Group normalizer
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /** @var array */
    protected $supportedFormats = ['internal_api'];

    /** @var NormalizerInterface */
    protected $groupNormalizer;

    /** @var StructureVersionProviderInterface */
    protected $structureVersionProvider;

    /** @var VersionManager */
    protected $versionManager;

    /** @var NormalizerInterface */
    protected $versionNormalizer;

    /**
     * @param NormalizerInterface               $groupNormalizer
     * @param StructureVersionProviderInterface $structureVersionProvider
     * @param VersionManager                    $versionManager
     * @param NormalizerInterface               $versionNormalizer
     */
    public function __construct(
        NormalizerInterface $groupNormalizer,
        StructureVersionProviderInterface $structureVersionProvider,
        VersionManager $versionManager,
        NormalizerInterface $versionNormalizer
    ) {
        $this->groupNormalizer = $groupNormalizer;
        $this->structureVersionProvider = $structureVersionProvider;
        $this->versionManager = $versionManager;
        $this->versionNormalizer = $versionNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($group, $format = null, array $context = [])
    {
        $normalizedGroup = $this->groupNormalizer->normalize($group, 'standard', $context);

        $normalizedGroup['products'] = [];
        foreach ($group->getProducts() as $product) {
            $normalizedGroup['products'][] = $product->getIdentifier();
        }

        $firstVersion = $this->versionManager->getOldestLogEntry($group);
        $lastVersion = $this->versionManager->getNewestLogEntry($group);

        $firstVersion = null !== $firstVersion ?
            $this->versionNormalizer->normalize($firstVersion, 'internal_api') :
            null;
        $lastVersion = null !== $lastVersion ?
            $this->versionNormalizer->normalize($lastVersion, 'internal_api') :
            null;

        $normalizedGroup['meta'] = [
            'id'                => $group->getId(),
            'form'              => 'pim-group-edit-form',
            'structure_version' => $this->structureVersionProvider->getStructureVersion(),
            'model_type'        => 'group',
            'created'           => $firstVersion,
            'updated'           => $lastVersion,
        ];

        return $normalizedGroup;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof GroupInterface && in_array($format, $this->supportedFormats);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
