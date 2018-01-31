<?php

namespace Pim\Bundle\EnrichBundle\Normalizer;

use Pim\Bundle\EnrichBundle\Provider\StructureVersion\StructureVersionProviderInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Family variant normalizer
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyVariantNormalizer implements NormalizerInterface
{
    /** @var array */
    protected $supportedFormats = ['internal_api'];

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var StructureVersionProviderInterface */
    protected $structureVersionProvider;

    /**
     * @param NormalizerInterface                $normalizer
     * @param StructuredVersionProviderInterface $structureVersionProvider
     */
    public function __construct(
        NormalizerInterface $normalizer,
        StructureVersionProviderInterface $structureVersionProvider
    ) {
        $this->normalizer = $normalizer;
        $this->structureVersionProvider = $structureVersionProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $result = $this->normalizer->normalize($object, 'standard', $context);

        $result['meta'] = [
            'structure_version' => $this->structureVersionProvider->getStructureVersion(),
            'form'              => 'pim-family-variant-edit-form',
            'id'                => $object->getId(),
            'model_type'        => 'family_variant',
        ];

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof FamilyVariantInterface && in_array($format, $this->supportedFormats);
    }
}
