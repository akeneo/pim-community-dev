<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize associations into an array
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ParentsAssociationsNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($associationAwareEntity, $format = null, array $context = [])
    {
        $parentAssociations = $this->getParentAssociations($associationAwareEntity);
        $data = [];

        foreach ($parentAssociations as $association) {
            $code = $association->getAssociationType()->getCode();
            if (!isset($data[$code]['groups'])) {
                $data[$code]['groups'] = [];
            }
            foreach ($association->getGroups() as $group) {
                $data[$code]['groups'][] = $group->getCode();
            }

            if (!isset($data[$code]['products'])) {
                $data[$code]['products'] = [];
            }
            foreach ($association->getProducts() as $product) {
                $data[$code]['products'][] = $product->getReference();
            }

            if (!isset($data[$code]['product_models'])) {
                $data[$code]['product_models'] = [];
            }
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

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    /**
     * @param EntityWithFamilyVariantInterface $product
     *
     * @return AssociationInterface[]
     */
    private function getParentAssociations(EntityWithFamilyVariantInterface $product): array
    {
        $parent = $product->getParent();
        $parentAssociations = [];

        if (null === $parent) {
            return $parentAssociations;
        }

        foreach ($parent->getAllAssociations() as $association) {
            $parentAssociations[] = $association;
        }

        return $parentAssociations;
    }
}
