<?php

namespace Pim\Bundle\EnrichBundle\Normalizer;

use Pim\Bundle\EnrichBundle\Provider\StructureVersion\StructureVersionProviderInterface;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Component\Catalog\Model\GroupInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Group normalizer
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupNormalizer implements NormalizerInterface
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
        $this->groupNormalizer          = $groupNormalizer;
        $this->structureVersionProvider = $structureVersionProvider;
        $this->versionManager           = $versionManager;
        $this->versionNormalizer        = $versionNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($group, $format = null, array $context = [])
    {
        $normalizedGroup = $this->groupNormalizer->normalize($group, 'json', $context);

        $normalizedGroup['products'] = [];
        foreach ($group->getProducts() as $product) {
            $normalizedGroup['products'][] = $product->getId();
        }

        $oldestLog = $this->versionManager->getOldestLogEntry($group);
        $newestLog = $this->versionManager->getNewestLogEntry($group);

        $created = null !== $oldestLog ? $this->versionNormalizer->normalize($oldestLog, 'internal_api') : null;
        $updated = null !== $newestLog ? $this->versionNormalizer->normalize($newestLog, 'internal_api') : null;

        $normalizedGroup['meta'] = [
            'id'                => $group->getId(),
            'form'              => 'pim-variant-group-edit-form',
            'structure_version' => $this->structureVersionProvider->getStructureVersion(),
            'model_type'        => 'variant_group',
            'created'           => $created,
            'updated'           => $updated,
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
}
