<?php

namespace Pim\Bundle\TransformBundle\Normalizer\MongoDB;

use Pim\Bundle\CatalogBundle\Model\Association;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;


use MongoId;

/**
 * Normalize a product association into a MongoDB Document
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return ($data instanceof Association && ProductNormalizer::FORMAT === $format);
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($assoc, $format = null, array $context = [])
    {
        $data = [];
        $data['_id'] = new MongoId;
        $data['associationType'] = $assoc->getAssociationType()->getId();
        $data['owner'] = MongoDBRef::create($context[ProductNormalizer::MONGO_ID]);

        $data['products'] = $this->normalizeProducts($association->getProducts());
        $data['groupIds'] = $this->normalizeGroups($assocation->getGroups());

        return $data;
    }

    /**
     * Get the products ids as an array of MongoDBRef
     *
     * @param ProductInterface[] $products
     *
     * @return array
     */
    protected function normalizeProducts($products) {
        $data = [];

        foreach ($products as $product) {
            $data[] = MongoDBRef::create($group->getId());
        }

        return $data;
    }

    /**
     * Get the groups ids as an array
     *
     * @param Group[] $groups
     *
     * @return array
     */
    protected function normalizeGroups($groups) {
        $data = [];

        foreach ($groups as $group) {
            $data[] = $group->getId();
        }

        return $data;
    }
}
