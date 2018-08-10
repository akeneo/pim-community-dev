<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize associations into an array
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationsNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     *
     * @param EntityWithAssociationsInterface $associationAwareEntity
     */
    public function normalize($associationAwareEntity, $format = null, array $context = [])
    {
        $data = [];

        foreach ($associationAwareEntity->getAllAssociations() as $association) {
            $code = $association->getAssociationType()->getCode();
            $data[$code]['groups'] = [];
            foreach ($association->getGroups() as $group) {
                $data[$code]['groups'][] = $group->getCode();
            }

            $data[$code]['products'] = [];
            foreach ($association->getProducts() as $product) {
                $data[$code]['products'][] = $product->getReference();
            }

            $data[$code]['product_models'] = [];
            foreach ($association->getProductModels() as $productModel) {
                $data[$code]['product_models'][] = $productModel->getCode();
            }
        }

        ksort($data);

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof EntityWithAssociationsInterface && 'standard' === $format;
    }
}
