<?php

namespace Pim\Bundle\CatalogBundle\MongoDB\Normalizer\Document;

use Akeneo\Bundle\StorageUtilsBundle\MongoDB\MongoObjectsFactory;
use Pim\Component\Catalog\Model\ProductInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * A transformer to normalize a product object into a MongoDB object
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    /** @staticvar string */
    const FORMAT = 'mongodb_document';

    /** @staticvar string */
    const MONGO_ID = '_id';

    /** @staticvar string */
    const MONGO_COLLECTION_NAME = 'collection_name';

    /** @staticvar string */
    const MONGO_DATABASE_NAME = 'database_name';

    /** @var NormalizerInterface */
    protected $normalizer;

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
    public function setSerializer(SerializerInterface $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return ($data instanceof ProductInterface && self::FORMAT === $format);
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($product, $format = null, array $context = [])
    {
        if (!$this->normalizer instanceof NormalizerInterface) {
            throw new \LogicException('Serializer must be a normalizer');
        }

        $data = [];

        if (null !== $product->getId()) {
            $data[self::MONGO_ID] = $this->mongoFactory->createMongoId($product->getId());
        } else {
            $data[self::MONGO_ID] = $this->mongoFactory->createMongoId();
        }

        $context[self::MONGO_ID] = $data[self::MONGO_ID];

        $data['created'] = $this->normalizer->normalize($product->getCreated(), self::FORMAT, $context);
        $data['updated'] = $this->normalizer->normalize($product->getUpdated(), self::FORMAT, $context);

        if (null !== $product->getFamily()) {
            $data['family'] = $product->getFamily()->getId();
        }

        $data['enabled'] = $product->isEnabled();

        $data['groupIds'] = $this->normalizeGroups($product->getGroups());
        $data['categoryIds'] = $this->normalizeCategories($product->getCategories());
        $data['associations'] = $this->normalizeAssociations($product->getAssociations(), $context);
        $data['values'] = $this->normalizeValues($product->getValues(), $context);
        $data['normalizedData'] = $this->normalizer->normalize($product, 'mongodb_json');
        $data['completenesses'] = [];

        unset($data['normalizedData']['completenesses']);

        return $data;
    }

    /**
     * Normalize the values of the product to MongoDB objects
     *
     * @param ProductValue[]|Collection $values
     * @param array                     $context
     *
     * @return array
     */
    protected function normalizeValues($values, array $context = [])
    {
        $data = [];

        foreach ($values as $value) {
            $valueData = $this->normalizer->normalize($value, self::FORMAT, $context);
            if (null !== $valueData) {
                $data[] = $valueData;
            }
        }

        return $data;
    }

    /**
     * Normalize the associations of the product
     *
     * @param Association[]|Collection $associations
     * @param array                    $context
     *
     * @return array
     */
    protected function normalizeAssociations($associations, array $context = [])
    {
        $data = [];

        foreach ($associations as $association) {
            $associationData = $this->normalizer->normalize($association, self::FORMAT, $context);
            if (null !== $associationData) {
                $data[] = $associationData;
            }
        }

        return $data;
    }

    /**
     * Normalize the groups of the product
     *
     * @param Group[]|Collection $groups
     *
     * @return array
     */
    protected function normalizeGroups($groups = [])
    {
        $data = [];

        foreach ($groups as $group) {
            $data[] = $group->getId();
        }

        return $data;
    }

    /**
     * Normalize the categories of the product
     *
     * @param Category[]|Collection $categories
     *
     * @return array
     */
    protected function normalizeCategories($categories = [])
    {
        $data = [];

        foreach ($categories as $category) {
            $data[] = $category->getId();
        }

        return $data;
    }
}
