<?php

namespace Pim\Bundle\TransformBundle\Normalizer\MongoDB;

use Akeneo\Bundle\StorageUtilsBundle\MongoDB\MongoObjectsFactory;
use Pim\Bundle\CatalogBundle\Model\ProductPriceInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize a product price into a MongoDB Document
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductPriceNormalizer implements NormalizerInterface
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
        return ($data instanceof ProductPriceInterface && ProductNormalizer::FORMAT === $format);
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($price, $format = null, array $context = [])
    {
        $data = [];
        $data['_id'] = $this->mongoFactory->createMongoId();
        $data['currency'] = $price->getCurrency();
        if (null !== $price->getData()) {
            $data['data'] = (float) $price->getData();
        }

        return $data;
    }
}
