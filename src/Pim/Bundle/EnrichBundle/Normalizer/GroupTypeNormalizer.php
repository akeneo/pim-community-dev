<?php

namespace Pim\Bundle\EnrichBundle\Normalizer;

use Pim\Bundle\EnrichBundle\Provider\StructureVersion\StructureVersionProviderInterface;
use Pim\Component\Catalog\Model\GroupTypeInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Group type normalizer
 *
 * @author    Tamara Robichet <filips@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupTypeNormalizer implements NormalizerInterface
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
            'structure_version' => $this->structureVersionProvider->getStructureVersion()
        ];

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof GroupTypeInterface && in_array($format, $this->supportedFormats);
    }
}
