<?php

namespace Pim\Bundle\TransformBundle\Normalizer\Structured;

use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileNormalizer implements NormalizerInterface
{
    /** @var string[] */
    protected $supportedFormats = ['json', 'xml'];

    /**
     * {@inheritdoc}
     */
    public function normalize($file, $format = null, array $context = [])
    {
        return [
            'filePath'         => $file->getKey(),
            'originalFilename' => $file->getOriginalFilename(),
            'hash'             => $file->getHash(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof FileInfoInterface && in_array($format, $this->supportedFormats);
    }
}
