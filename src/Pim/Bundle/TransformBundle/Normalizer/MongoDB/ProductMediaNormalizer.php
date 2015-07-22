<?php

namespace Pim\Bundle\TransformBundle\Normalizer\MongoDB;

use Pim\Bundle\CatalogBundle\Model\ProductMedia;
use Pim\Bundle\CatalogBundle\MongoDB\MongoObjectsFactory;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize a product media entity into an MongoDB Document
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductMediaNormalizer implements NormalizerInterface
{
    /** @var MongoObjectsFactory */
    protected $mongoFactory;

    /**
     * @param MongoObjectsFactory $mongoFactory
     */
    public function __construct(MongoObjectsFactory $mongoFactory)
    {
        $this->mongoFactory = $mongoFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return ($data instanceof ProductMedia && ProductNormalizer::FORMAT === $format);
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($media, $format = null, array $context = [])
    {
        $data = [];
        $data['_id']              = $this->mongoFactory->createMongoId();
        $data['filename']         = $media->getFilename();
        // @deprecated will be removed in 1.3
        $data['filePath']         = $media->getFilePath();
        $data['originalFilename'] = $media->getOriginalFilename();
        $data['mimeType']         = $media->getMimeType();

        return $data;
    }
}
