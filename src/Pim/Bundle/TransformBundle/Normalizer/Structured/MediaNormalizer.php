<?php

namespace Pim\Bundle\TransformBundle\Normalizer\Structured;

use Pim\Bundle\CatalogBundle\Model\ProductMediaInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize a media entity into an array
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MediaNormalizer implements NormalizerInterface
{
    /**
     * @var string[] $supportedFormats
     */
    protected $supportedFormats = ['json', 'xml'];

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $file = $object->getFile();
        if (null !== $file && $file instanceof UploadedFile) {
            // happens in case of mass edition
            return [
                'originalFilename' => $file->getClientOriginalName(),
                'filePath' => $file->getPathname(),
            ];
        }

        return [
            'originalFilename' => $object->getOriginalFilename(),
            'filePath' => $object->getFilePath(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductMediaInterface && in_array($format, $this->supportedFormats);
    }
}
