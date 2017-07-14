<?php

namespace Pim\Bundle\EnrichBundle\Normalizer;

use Akeneo\Component\Localization\Localizer\LocalizerInterface;
use Pim\Bundle\EnrichBundle\Provider\EmptyValue\EmptyValueProviderInterface;
use Pim\Bundle\EnrichBundle\Provider\Field\FieldProviderInterface;
use Pim\Bundle\EnrichBundle\Provider\Filter\FilterProviderInterface;
use Pim\Bundle\EnrichBundle\Provider\StructureVersion\StructureVersionProviderInterface;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Component\Catalog\Model\AttributeInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Attribute normalizer
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeNormalizer implements NormalizerInterface
{
    /** @var array $supportedFormats */
    protected $supportedFormats = ['internal_api'];

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var FieldProviderInterface */
    protected $fieldProvider;

    /** @var EmptyValueProviderInterface */
    protected $emptyValueProvider;

    /** @var FilterProviderInterface */
    protected $filterProvider;

    /** @var VersionManager */
    protected $versionManager;

    /** @var NormalizerInterface */
    protected $versionNormalizer;

    /** @var StructureVersionProviderInterface */
    protected $structureVersionProvider;

    /** @var LocalizerInterface */
    protected $numberLocalizer;

    /**
     * @param NormalizerInterface               $normalizer
     * @param FieldProviderInterface            $fieldProvider
     * @param EmptyValueProviderInterface       $emptyValueProvider
     * @param FilterProviderInterface           $filterProvider
     * @param VersionManager                    $versionManager
     * @param NormalizerInterface               $versionNormalizer
     * @param StructureVersionProviderInterface $structureVersionProvider
     * @param LocalizerInterface                $numberLocalizer
     */
    public function __construct(
        NormalizerInterface $normalizer,
        FieldProviderInterface $fieldProvider,
        EmptyValueProviderInterface $emptyValueProvider,
        FilterProviderInterface $filterProvider,
        VersionManager $versionManager,
        NormalizerInterface $versionNormalizer,
        StructureVersionProviderInterface $structureVersionProvider,
        LocalizerInterface $numberLocalizer
    ) {
        $this->normalizer = $normalizer;
        $this->fieldProvider = $fieldProvider;
        $this->emptyValueProvider = $emptyValueProvider;
        $this->filterProvider = $filterProvider;
        $this->versionManager = $versionManager;
        $this->versionNormalizer = $versionNormalizer;
        $this->structureVersionProvider = $structureVersionProvider;
        $this->numberLocalizer = $numberLocalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($attribute, $format = null, array $context = [])
    {
        $dateMin = null === $attribute->getDateMin() ? null : $attribute->getDateMin()->format('Y-m-d');
        $dateMax = null === $attribute->getDateMax() ? null : $attribute->getDateMax()->format('Y-m-d');

        $normalizedAttribute = array_merge(
            $this->normalizer->normalize($attribute, 'standard', $context),
            [
                'empty_value'        => $this->emptyValueProvider->getEmptyValue($attribute),
                'field_type'         => $this->fieldProvider->getField($attribute),
                'filter_types'       => $this->filterProvider->getFilters($attribute),
                'is_locale_specific' => $attribute->isLocaleSpecific(),
                'date_min'           => $dateMin,
                'date_max'           => $dateMax,
            ]
        );

        if (isset($context['locale'])) {
            $normalizedAttribute['number_min'] = $this->numberLocalizer->localize(
                $normalizedAttribute['number_min'],
                ['locale' => $context['locale']]
            );

            $normalizedAttribute['number_max'] = $this->numberLocalizer->localize(
                $normalizedAttribute['number_max'],
                ['locale' => $context['locale']]
            );
        }

        $firstVersion = $this->versionManager->getOldestLogEntry($attribute);
        $lastVersion = $this->versionManager->getNewestLogEntry($attribute);

        $firstVersion = null !== $firstVersion ?
            $this->versionNormalizer->normalize($firstVersion, 'internal_api') :
            null;
        $lastVersion = null !== $lastVersion ?
            $this->versionNormalizer->normalize($lastVersion, 'internal_api') :
            null;

        $normalizedAttribute['meta'] = [
            'id'                => $attribute->getId(),
            'created'           => $firstVersion,
            'updated'           => $lastVersion,
            'structure_version' => $this->structureVersionProvider->getStructureVersion(),
            'model_type'        => 'attribute',
        ];

        return $normalizedAttribute;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof AttributeInterface && in_array($format, $this->supportedFormats);
    }
}
