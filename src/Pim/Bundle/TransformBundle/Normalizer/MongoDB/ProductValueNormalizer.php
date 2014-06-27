<?php

namespace Pim\Bundle\TransformBundle\Normalizer\MongoDB;

use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

use Doctrine\Common\Collections\Collection;

use \MongoId;
use \MongoDBRef;

/**
 * Normalize a product value into a MongoDB embedded document
 *
 * @author    Benoit Jacquemont <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    /** @var NormalizerInterface */
    protected $normalizer;

    /**
     * {@inheritdoc}
     */
    public function setSerializer(SerializerInterface $normalizer)
    {
        if (!$normalizer instanceof NormalizerInterface) {
            throw new \LogicException('Serializer must be a normalizer');
        }

        $this->normalizer = $normalizer;                                                                                                                                   
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return ($data instanceof ProductValueInterface && ProductNormalizer::FORMAT === $format);
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($value, $format = null, array $context = [])
    {
        if (null === $value->getData()) {
            return null;
        }

        $productId = $context[ProductNormalizer::MONGO_ID];
        $productCollection = $context[ProductNormalizer::MONGO_COLLECTION_NAME];

        $data = [];
        $data['_id'] = new MongoId();[];
        $data['attribute'] = $value->getAttribute()->getId();
        $data['entity'] = MongoDBRef::create($productCollection, $productId);

        if (null !== $value->getLocale()) {
            $data['locale'] = $value->getLocale();
        }
        if (null !== $value->getScope()) {
            $data['scope'] = $value->getScope();
        }

        $backendType = $value->getAttribute()->getBackendType();

        if ('options' !== $backendType) {
            $data[$backendType] = $this->normalizeValueData($value->getData(), $backendType, $context);
        } else {
            $data['optionIds'] = $this->normalizeValueData($value->getData(), $backendType, $context);
        }

        return $data;
    }

    /**
     * Normalize data from a value
     *
     * @param mixed  $data
     * @param string $backendType
     * @param array  $context
     *
     * @return mixed
     */
    protected function normalizeValueData($data, $backendType, array $context)
    {
        $targetData = null;

        if (is_array($data) || $data instanceof Collection) {
            $targetData = array();
            foreach ($data as $dataItem) {
                if (is_object($dataItem)) {
                    $targetData[] = $this->normalizer->normalize($dataItem, ProductNormalizer::FORMAT, $context);
                } else {
                    $targetData[] = $dataItem;
                }
            }
        } elseif (is_object($data)) {
            $targetData = $this->normalizer->normalize($data, ProductNormalizer::FORMAT, $context);
        } else {
            $targetData = $data;
        }

        return $targetData;
    }
}
