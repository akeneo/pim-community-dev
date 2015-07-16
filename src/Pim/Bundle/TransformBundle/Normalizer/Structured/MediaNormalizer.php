<?php

namespace Pim\Bundle\TransformBundle\Normalizer\Structured;

use Pim\Bundle\CatalogBundle\Manager\MediaManager;
use Pim\Bundle\CatalogBundle\Model\ProductMediaInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize a media entity into an array
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * TODO: should be deleted
 */
class MediaNormalizer implements NormalizerInterface
{
    /** @var MediaManager */
    protected $manager;

    /**
     * @param MediaManager $manager
     */
    public function __construct(MediaManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @var string[]
     */
    protected $supportedFormats = ['json', 'xml'];

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $file = $object->getFile();
        if (null !== $file && $file instanceof UploadedFile) {
            //TODO: normally this part should be removed
            // happens in case of mass edition
            return [
                'originalFilename' => $file->getClientOriginalName(),
                'filePath' => $file->getPathname(),
            ];
        } elseif (null !== $file) {
            return [
                'originalFilename' => $file->getOriginalFilename(),
                'filePath' => $file->getKey(),
            ];
        }

        return [
            'originalFilename' => null,
            'filePath'         => null,
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
