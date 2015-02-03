<?php

namespace Pim\Bundle\TransformBundle\Denormalizer\Structured\ProductValue;

use Pim\Bundle\CatalogBundle\Manager\MediaManager;

/**
 * Product media denormalizer used for following attribute type:
 * - pim_catalog_file
 * - pim_catalog_image
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MediaDenormalizer extends AbstractValueDenormalizer
{
    /** @var MediaManager */
    protected $mediaManager;

    /**
     * @param array        $supportedTypes
     * @param MediaManager $mediaManager
     */
    public function __construct(array $supportedTypes, MediaManager $mediaManager)
    {
        parent::__construct($supportedTypes);

        $this->mediaManager = $mediaManager;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        if (empty($data)) {
            return null;
        }

        $media = $this->mediaManager->createFromFilePath($data['filePath']);
        $media->setOriginalFilename($data['originalFilename']);

        return $media;
    }
}
