<?php

namespace Pim\Bundle\VersioningBundle\Normalizer\Flat;

use Pim\Component\Catalog\Model\LocaleInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Pim\Component\Catalog\Normalizer\Standard\LocaleNormalizer as StandardNormalizer;

/**
 * Normalize a locale
 *
 * @author    Sanchez Julien <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocaleNormalizer implements NormalizerInterface
{
    /** @var string[] */
    protected $supportedFormats = ['flat'];

    /** @var StandardNormalizer */
    protected $standardNormalizer;

    /**
     * @param NormalizerInterface $standardNormalizer
     */
    public function __construct(NormalizerInterface $standardNormalizer)
    {
        $this->standardNormalizer = $standardNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        return $this->standardNormalizer->normalize($object, 'standard', $context);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof LocaleInterface && in_array($format, $this->supportedFormats);
    }
}
