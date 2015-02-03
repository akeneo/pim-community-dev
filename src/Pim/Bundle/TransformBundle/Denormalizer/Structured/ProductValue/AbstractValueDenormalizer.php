<?php

namespace Pim\Bundle\TransformBundle\Denormalizer\Structured\ProductValue;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * Abstract value denormalizer
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractValueDenormalizer implements DenormalizerInterface
{
    /** @var string[] */
    protected $supportedFormats = ['json'];

    /** @var string[] */
    protected $supportedTypes;

    /**
     * @param string[] $supportedTypes
     */
    public function __construct(array $supportedTypes)
    {
        $this->supportedTypes = $supportedTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return in_array($type, $this->supportedTypes) && in_array($format, $this->supportedFormats);
    }
}
