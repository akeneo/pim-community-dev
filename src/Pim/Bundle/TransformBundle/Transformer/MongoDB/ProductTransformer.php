<?php

namespace Pim\Bundle\TransformBundle\Transformer\MongoDB;

use Pim\Bundle\TransformBundle\Transformer\ObjectTransformerInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

use Symfony\Component\Serializer\SerializerInterface;

use \MongoId;
use \MongoDate;

/**
 * A transfomer to transform a product object into a MongoDB object
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductTransformer implements ObjectTransformerInterface
{
    /** @staticvar string */
    const MONGO_ID = '_id';

    /** @staticvar string */
    const MONGO_COLLECTION_NAME = 'collection_name';
    
    /** @var ProductValueTransformer */
    protected $valueTransformer;

    /** @var DateTimeTransformer */
    protected $dateTransformer;

    /** @var SerializerInterface */
    protected $serializer;

    /**
     * @param ProductValueTransformer $valueTransformer
     * @param DateTimeTransformer     $dateTransformer
     * @param SerializerInterface     $serializer
     */
    public function __construct(
        ProductValueTransformer $valueTransformer,
        DateTimeTransformer $dateTransformer,
        SerializerInterface $serializer
    ) {
        $this->valueTransformer = $valueTransformer;
        $this->serializer       = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($product, array $context = [])
    {
        $target = new \StdClass();

        $target->_id = null !== $product->getId() ? new MongoId($product->getId()) : new MongoId();
        $context[self::MONGO_ID] = $target->_id;

        if (null !== $product->getCreated()) {
            $target->created = $this->dateTransformer->transform($product->getCreated());
        } else {
            $target->created = new \MongoDate();
        }

        if (null !== $product->getUpdated()) {
            $target->updated = $this->dateTransformer->transform($product->getUpdated());
        } else {
            $target->updated = new \MongoDate();
        }

        $target->family         = $product->getFamily() ? $product->getFamily()->getId() : null;
        $target->groupIds       = $this->transformGroups($product->getGroups());
        $target->categoryIds    = $this->transformCategories($product->getCategories());
        $target->enabled        = $product->isEnabled();
        $target->associations   = $this->transformAssociations($product->getAssociations());
        $target->values         = $this->transformValues($product->getValues(), $context);
        $target->normalizedData = $this->serializer->normalize($product, 'mongodb_json');

        return $target;
    }

    /**
     * Transform the values of the product to MongoDB objects
     *
     * @param ArrayCollection $values
     * @param array           $context
     *
     * @return array
     */
    protected function transformValues($values, array $context = [])
    {
        $targetValues = [];

        foreach ($values as $value) {
            $targetValue = $this->valueTransformer->transform($value, $context);
            if (null !== $targetValue) {
                $targetValues[] = $targetValue;
            }
        }

        return $targetValues;
    }

    /**
     * Transform the associations of the product
     *
     * @param Association[] $associations
     *
     * @return array
     */
    protected function transformAssociations($associations = [])
    {
        $targetAssociations = [];

        foreach ($associations as $association) {
            $code = $association->getAssociationType()->getCode();

            foreach ($association->getGroups() as $group) {
                $targetAssociations[$code]['groups'][] = $group->getCode();
            }

            foreach ($association->getProducts() as $product) {
                $targetAssociations[$code]['products'][] = $product->getReference();
            }
        }

        return $targetAssociations;
    }

    /**
     * Transform the groups of the product
     *
     * @param Group[] $groups
     *
     * @return array
     */
    protected function transformGroups($groups = [])
    {
        $targetGroups = [];

        foreach ($groups as $group) {
            $targetGroups[] = $group->getId();
        }

        return $targetGroups;
    }

    /**
     * Transform the categories of the product
     *
     * @param Category[] $categories
     *
     * @return array
     */
    protected function transformCategories($categories = [])
    {
        $targetGroups = [];

        foreach ($categories as $category) {
            $targetGroups[] = $category->getId();
        }

        return $targetGroups;
    }
}
