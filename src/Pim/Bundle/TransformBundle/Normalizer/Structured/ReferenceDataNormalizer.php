<?php

namespace Pim\Bundle\TransformBundle\Normalizer\Structured;

use Pim\Component\ReferenceData\Model\ReferenceDataInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataNormalizer implements NormalizerInterface
{
    /** @var string[] $supportedFormats */
    protected $supportedFormats = ['json', 'xml'];

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        if (isset($context['entity']) && $context['entity'] === 'product') {
            return $object->getCode();
        }

        return [
            'code' => $object->getCode()
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ReferenceDataInterface && in_array($format, $this->supportedFormats);
    }
}
