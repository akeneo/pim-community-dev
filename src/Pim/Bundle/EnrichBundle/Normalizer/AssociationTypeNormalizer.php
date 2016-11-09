<?php

namespace Pim\Bundle\EnrichBundle\Normalizer;

use Pim\Component\Catalog\Model\AssociationTypeInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Association type normalizer
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationTypeNormalizer implements NormalizerInterface
{
    /** @var array */
    protected $supportedFormats = ['internal_api'];

    /** @var NormalizerInterface */
    protected $normalizer;

    /**
     * @param NormalizerInterface $normalizer
     */
    public function __construct(NormalizerInterface $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $result = $this->normalizer->normalize($object, 'standard', $context);
        $result['id'] = $object->getId();

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof AssociationTypeInterface && in_array($format, $this->supportedFormats);
    }
}
