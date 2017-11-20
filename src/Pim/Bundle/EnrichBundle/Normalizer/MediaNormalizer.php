<?php

namespace Pim\Bundle\EnrichBundle\Normalizer;

use Pim\Component\Catalog\Value\MediaValue;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * MediaNormalizer.php
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MediaNormalizer implements NormalizerInterface
{
    /** @var FileNormalizer */
    protected $fileNormalizer;

    /** @var string[] */
    protected $supportedFormats = ['internal_api'];

    /**
     * @param FileNormalizer $fileNormalizer
     */
    public function __construct(FileNormalizer $fileNormalizer)
    {
        $this->fileNormalizer = $fileNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($value, $format = null, array $context = [])
    {
        if (null === $value->getData()) {
            return null;
        }

        return $this->fileNormalizer->normalize($value->getData(), $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        error_log('SUPPRPRPRPRR');

        return $data instanceof MediaValue && in_array($format, $this->supportedFormats);
    }
}
