<?php

namespace Pim\Bundle\CatalogBundle\MongoDB\Normalizer\Document;

use Akeneo\Bundle\StorageUtilsBundle\MongoDB\MongoObjectsFactory;
use Pim\Component\Catalog\Model\AssociationInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize a product association into a MongoDB Document
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationNormalizer implements NormalizerInterface
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
        return ($data instanceof AssociationInterface && ProductNormalizer::FORMAT === $format);
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($assoc, $format = null, array $context = [])
    {
        $productId = $context[ProductNormalizer::MONGO_ID];
        $productCollection = $context[ProductNormalizer::MONGO_COLLECTION_NAME];
        $databaseName = $context[ProductNormalizer::MONGO_DATABASE_NAME];

        $data = [];
        $data['_id'] = $this->mongoFactory->createMongoId();
        $data['associationType'] = $assoc->getAssociationType()->getId();
        $data['owner'] = $this->mongoFactory->createMongoDBRef($productCollection, $productId);

        $data['products'] = $this->normalizeProducts($assoc->getProducts(), $productCollection, $databaseName);
        $data['groupIds'] = $this->normalizeGroups($assoc->getGroups());

        return $data;
    }

    /**
     * Get the products ids as an array of MongoDBRef
     *
     * @param Collection|ProductInterface[] $products
     * @param string                        $productCollection
     *
     * @return array
     */
    protected function normalizeProducts($products, $productCollection, $databaseName)
    {
        $data = [];

        foreach ($products as $product) {
            $data[] = $this->mongoFactory->createMongoDBRef(
                $productCollection,
                $this->mongoFactory->createMongoId($product->getId()),
                $databaseName
            );
        }

        return $data;
    }

    /**
     * Get the groups ids as an array
     *
     * @param Collection|Group[] $groups
     *
     * @return array
     */
    protected function normalizeGroups($groups)
    {
        $data = [];

        foreach ($groups as $group) {
            $data[] = $group->getId();
        }

        return $data;
    }
}
