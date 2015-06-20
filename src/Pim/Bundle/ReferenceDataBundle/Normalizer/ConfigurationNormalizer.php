<?php

namespace Pim\Bundle\ReferenceDataBundle\Normalizer;

use Pim\Component\ReferenceData\Model\Configuration;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Configuration normalizer
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConfigurationNormalizer implements NormalizerInterface
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
        return $data instanceof Configuration && in_array($format, $this->supportedFormats);
    }
}
