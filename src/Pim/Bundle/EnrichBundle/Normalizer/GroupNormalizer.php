<?php

namespace Pim\Bundle\EnrichBundle\Normalizer;

use Pim\Bundle\EnrichBundle\Provider\StructureVersion\StructureVersionProviderInterface;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Component\Catalog\Localization\Localizer\AttributeConverterInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Enrich\Converter\ConverterInterface;
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

    /** @var AttributeConverterInterface */
    protected $localizedConverter;

    /** @var ConverterInterface */
    protected $productValueConverter;

    /**
     * @param NormalizerInterface               $groupNormalizer
     * @param StructureVersionProviderInterface $structureVersionProvider
     * @param VersionManager                    $versionManager
     * @param NormalizerInterface               $versionNormalizer
     * @param AttributeConverterInterface       $localizedConverter
     * @param ConverterInterface                $productValueConverter
     */
    public function __construct(
        NormalizerInterface $groupNormalizer,
        StructureVersionProviderInterface $structureVersionProvider,
        VersionManager $versionManager,
        NormalizerInterface $versionNormalizer,
        AttributeConverterInterface $localizedConverter,
        ConverterInterface $productValueConverter
    ) {
        $this->groupNormalizer = $groupNormalizer;
        $this->structureVersionProvider = $structureVersionProvider;
        $this->versionManager = $versionManager;
        $this->versionNormalizer = $versionNormalizer;
        $this->localizedConverter = $localizedConverter;
        $this->productValueConverter = $productValueConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($group, $format = null, array $context = [])
    {
        $normalizedGroup = $this->groupNormalizer->normalize($group, 'standard', $context);
        if (isset($normalizedGroup['values'])) {
            $normalizedGroup['values'] = $this->localizedConverter->convertToLocalizedFormats(
                $normalizedGroup['values'],
                $context
            );

            $normalizedGroup['values'] = $this->productValueConverter->convert($normalizedGroup['values']);
        }

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

        $form = $group->getType()->isVariant() ? 'pim-variant-group-edit-form' : 'pim-group-edit-form';
        $modelType = $group->getType()->isVariant() ? 'variant_group' : 'group';

        $normalizedGroup['meta'] = [
            'id'                => $group->getId(),
            'form'              => $form,
            'structure_version' => $this->structureVersionProvider->getStructureVersion(),
            'model_type'        => $modelType,
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
}
