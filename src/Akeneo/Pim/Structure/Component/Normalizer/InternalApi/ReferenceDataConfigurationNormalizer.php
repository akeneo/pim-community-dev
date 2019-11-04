<?php

namespace Akeneo\Pim\Structure\Component\Normalizer\InternalApi;

use Akeneo\Pim\Structure\Component\Model\ReferenceDataConfiguration;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * ReferenceData Configuration normalizer
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataConfigurationNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /** @var string[] */
    protected $supportedFormats = ['internal_api'];

    /**
     * {@inheritdoc}
     */
    public function normalize($config, $format = null, array $context = [])
    {
        return [
            'name'  => $config->getName(),
            'type'  => $config->getType(),
            'class' => $config->getClass()
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ReferenceDataConfiguration && in_array($format, $this->supportedFormats);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
