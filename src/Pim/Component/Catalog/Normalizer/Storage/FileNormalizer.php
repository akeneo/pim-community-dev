<?php

namespace Pim\Component\Catalog\Normalizer\Storage;

use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileNormalizer implements NormalizerInterface
{
    /** @var NormalizerInterface */
    protected $stdNormalizer;

    /**
     * @param NormalizerInterface $normalizer
     */
    public function __construct(NormalizerInterface $normalizer)
    {
        $this->stdNormalizer = $normalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($file, $format = null, array $context = [])
    {
        return $this->stdNormalizer->normalize($file, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof FileInfoInterface && 'storage' === $format;
    }
}
