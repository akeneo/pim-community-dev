<?php

namespace Pim\Bundle\VersioningBundle\Normalizer\Flat;

use Pim\Component\Catalog\Model\LocaleInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

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

    /** @var NormalizerInterface */
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
     *
     * @param LocaleInterface $locale
     *
     * @return array
     */
    public function normalize($locale, $format = null, array $context = [])
    {
        return $this->standardNormalizer->normalize($locale, 'standard', $context);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof LocaleInterface && in_array($format, $this->supportedFormats);
    }
}
