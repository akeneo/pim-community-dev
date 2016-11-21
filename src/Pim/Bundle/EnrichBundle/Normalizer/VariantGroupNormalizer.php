<?php

namespace Pim\Bundle\EnrichBundle\Normalizer;

use Pim\Bundle\EnrichBundle\Provider\StructureVersion\StructureVersionProviderInterface;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Component\Catalog\Localization\Localizer\AttributeConverterInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Variant Group normalizer
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupNormalizer implements NormalizerInterface
{
    /** @var array */
    protected $supportedFormats = ['internal_api'];

    /** @var NormalizerInterface */
    protected $variantGroupNormalizer;

    /** @var StructureVersionProviderInterface */
    protected $structureVersionProvider;

    /** @var VersionManager */
    protected $versionManager;

    /** @var NormalizerInterface */
    protected $versionNormalizer;

    /** @var AttributeConverterInterface */
    protected $localizedConverter;

    /**
     * @param NormalizerInterface               $variantGroupNormalizer
     * @param StructureVersionProviderInterface $structureVersionProvider
     * @param VersionManager                    $versionManager
     * @param NormalizerInterface               $versionNormalizer
     * @param AttributeConverterInterface       $localizedConverter
     */
    public function __construct(
        NormalizerInterface $variantGroupNormalizer,
        StructureVersionProviderInterface $structureVersionProvider,
        VersionManager $versionManager,
        NormalizerInterface $versionNormalizer,
        AttributeConverterInterface $localizedConverter
    ) {
        $this->variantGroupNormalizer = $variantGroupNormalizer;
        $this->structureVersionProvider = $structureVersionProvider;
        $this->versionManager = $versionManager;
        $this->versionNormalizer = $versionNormalizer;
        $this->localizedConverter = $localizedConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($group, $format = null, array $context = [])
    {
        $normalizedGroup = $this->variantGroupNormalizer->normalize($group, 'standard', $context);
        if (isset($normalizedGroup['values'])) {
            $normalizedGroup['values'] = $this->localizedConverter->convertToLocalizedFormats(
                $normalizedGroup['values'],
                $context
            );
        }

        $normalizedGroup['products'] = [];
        foreach ($group->getProducts() as $product) {
            $normalizedGroup['products'][] = $product->getId();
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
            'form'              => 'pim-variant-group-edit-form',
            'structure_version' => $this->structureVersionProvider->getStructureVersion(),
            'model_type'        => 'variant_group',
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
        return $data instanceof GroupInterface && $data->getType()->isVariant() && in_array($format, $this->supportedFormats);
    }
}
