<?php

namespace Pim\Bundle\CatalogBundle\MongoDB\Normalizer\Document;

use Akeneo\Bundle\StorageUtilsBundle\MongoDB\MongoObjectsFactory;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

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

    /** @var MongoObjectsFactory */
    protected $mongoFactory;

    /** @var ManagerRegistry */
    protected $managerRegistry;

    /**
     * @param MongoObjectsFactory $mongoFactory
     * @param ManagerRegistry     $managerRegistry
     */
    public function __construct(MongoObjectsFactory $mongoFactory, ManagerRegistry $managerRegistry)
    {
        $this->mongoFactory = $mongoFactory;
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function setSerializer(SerializerInterface $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return ($data instanceof ValueInterface && ProductNormalizer::FORMAT === $format);
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($value, $format = null, array $context = [])
    {
        if (!$this->normalizer instanceof NormalizerInterface) {
            throw new \LogicException('Serializer must be a normalizer');
        }

        $productCollection = $context[ProductNormalizer::MONGO_COLLECTION_NAME];
        $productId = $context[ProductNormalizer::MONGO_ID];
        $databaseName = $context[ProductNormalizer::MONGO_DATABASE_NAME];

        $data = [];
        $data['_id'] = $this->mongoFactory->createMongoId();
        $data['attribute'] = $value->getAttribute()->getId();
        $data['entity'] = $this->mongoFactory->createMongoDBRef($productCollection, $productId, $databaseName);

        if (null !== $value->getLocale()) {
            $data['locale'] = $value->getLocale();
        }
        if (null !== $value->getScope()) {
            $data['scope'] = $value->getScope();
        }

        $attribute = $value->getAttribute();
        $backendType = $attribute->getBackendType();
        $key = $this->getKeyForValue($value, $attribute, $backendType);
        $data[$key] = $this->normalizeValueData($value->getData(), $backendType, $context);

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
            $targetData = [];
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

    /**
     * Decide what is the key used for data inside the normalized product value
     *
     * @param ValueInterface     $value
     * @param AttributeInterface $attribute
     * @param string             $backendType
     *
     * @return string
     */
    protected function getKeyForValue(ValueInterface $value, AttributeInterface $attribute, $backendType)
    {
        if ('options' === $backendType) {
            return 'optionIds';
        }

        $refDataName = $attribute->getReferenceDataName();
        if (null === $refDataName) {
            return $backendType;
        }

        if ('reference_data_options' === $backendType) {
            return $this->getReferenceDataFieldName($value, $refDataName);
        }

        return $refDataName;
    }

    /**
     * Search in Doctrine mapping what is the field name defined for the specified reference data
     *
     * @param ValueInterface $value
     * @param string         $refDataName
     *
     * @throws \LogicException
     *
     * @return string
     */
    protected function getReferenceDataFieldName(ValueInterface $value, $refDataName)
    {
        $valueClass = ClassUtils::getClass($value);
        $manager = $this->managerRegistry->getManagerForClass($valueClass);
        $metadata = $manager->getClassMetadata($valueClass);
        $fieldName = $metadata->getFieldMapping($refDataName);

        if (!isset($fieldName['idsField'])) {
            throw new \LogicException(sprintf('No field name defined for reference data "%s"', $refDataName));
        }

        return $fieldName['idsField'];
    }
}
