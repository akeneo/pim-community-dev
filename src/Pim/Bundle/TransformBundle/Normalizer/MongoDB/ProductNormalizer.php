<?php

namespace Pim\Bundle\TransformBundle\Normalizer\MongoDB;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

use \MongoId;
use \MongoDate;

/**
 * A transfomer to normalize a product object into a MongoDB object
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    const FORMAT = "mongodb_document";

    /** @staticvar string */
    const MONGO_ID = '_id';

    /** @staticvar string */
    const MONGO_COLLECTION_NAME = 'collection_name';
    
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
        return ($data instanceof ProductInterface && self::FORMAT === $format);
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($product, $format = null, array $context = [])
    {
        $data = [];

        $data[self::MONGO_ID] = null !== $product->getId() ? new MongoId($product->getId()) : new MongoId();

        $context[self::MONGO_ID] = $data[self::MONGO_ID];

        if (null !== $product->getCreated()) {
            $data['created'] = $this->normalizer->normalize($product->getCreated(), self::FORMAT, $context);
        } else {
            $data['created'] = new \MongoDate();
        }

        $data['updated'] = new \MongoDate();

        $data['family']         = $product->getFamily() ? $product->getFamily()->getId() : null;
        $data['enabled']        = $product->isEnabled();

        $data['groupIds']       = $this->normalizeGroups($product->getGroups());
        $data['categoryIds']    = $this->normalizeCategories($product->getCategories());
        $data['associations']   = $this->normalizeAssociations($product->getAssociations(), $context);
        $data['values']         = $this->normalizeValues($product->getValues(), $context);
        $data['normalizedData'] = $this->normalizer->normalize($product, 'mongodb_json');
        $data['completenesses'] = [];

        unset($data['normalizedData']['completenesses']);

        return $data;
    }

    /**
     * Normalize the values of the product to MongoDB objects
     *
     * @param ArrayCollection $values
     * @param array           $context
     *
     * @return array
     */
    protected function normalizeValues($values, array $context)
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
     * @param Association[] $associations
     *
     * @return array
     */
    protected function normalizeAssociations($associations, array $context)
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
     * @param Group[] $groups
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
     * @param Category[] $categories
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
