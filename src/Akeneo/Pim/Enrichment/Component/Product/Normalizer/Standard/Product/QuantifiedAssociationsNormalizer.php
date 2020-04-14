<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product;

use Akeneo\Pim\Enrichment\Component\Product\Association\Query\GetAssociatedProductCodesByProduct;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize associations into an array
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class QuantifiedAssociationsNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    // /** @var GetAssociatedProductCodesByProduct */
    // private $getAssociatedProductCodeByProduct;

    // public function __construct(GetAssociatedProductCodesByProduct $getAssociatedProductCodeByProduct)
    // {
    //     $this->getAssociatedProductCodeByProduct = $getAssociatedProductCodeByProduct;
    // }

    /**
     * {@inheritdoc}
     *
     * @param EntityWithAssociationsInterface $associationAwareEntity
     */
    public function normalize($associationAwareEntity, $format = null, array $context = [])
    {
        $ancestorProducts = $this->getAncestorProducts($associationAwareEntity);
        $data = $this->normalizeQuantifiedAssociations($ancestorProducts);

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
     * @param EntityWithFamilyVariantInterface $entityWithFamilyVariant
     *
     * @return array|EntityWithFamilyVariantInterface[]
     */
    private function getAncestorProducts(EntityWithFamilyVariantInterface $entityWithFamilyVariant): array
    {
        $parent = $entityWithFamilyVariant->getParent();

        if (null === $parent) {
            return [$entityWithFamilyVariant];
        }

        return array_merge($this->getAncestorProducts($parent), [$entityWithFamilyVariant]);
    }

    /**
     * @param array $associationAwareEntities
     *
     * @return array
     */
    private function normalizeQuantifiedAssociations(array $associationAwareEntities)
    {
        $data = [];

        foreach ($associationAwareEntities as $associationAwareEntity) {
            foreach ($associationAwareEntity->getQuantifiedAssociations() as $quantifiedAssociation) {
                $code = $quantifiedAssociation->getAssociationType()->getCode();

                $data[$code]['products'] = $data[$code]['products'] ?? [];
                if ($associationAwareEntity instanceof ProductModelInterface) {
                    foreach ($quantifiedAssociation->getQuantifiedProducts() as $quantifiedProduct) {
                        $data[$code]['products'][] = [
                            'identifier' => $quantifiedProduct->getProduct()->getReference(),
                            'quantity' => $quantifiedProduct->getQuantity()
                        ];
                    }
                } else {
                    //add optim
                    foreach ($quantifiedAssociation->getQuantifiedProducts() as $quantifiedProduct) {
                        $data[$code]['products'][] = [
                            'identifier' => $quantifiedProduct->getProduct()->getReference(),
                            'quantity' => $quantifiedProduct->getQuantity()
                        ];
                    }
                }

                // $data[$code]['product_models'] = $data[$code]['product_models'] ?? [];
                // foreach ($quantifiedAssociation->getProductModels() as $productModel) {
                //     $data[$code]['product_models'][] = $productModel->getCode();
                // }
            }
        }

        $data = array_map(function ($quantifiedAssociation) {
            $quantifiedAssociation['products'] = array_unique($quantifiedAssociation['products']);

            return $quantifiedAssociation;
        }, $data);

        ksort($data);

        return $data;
    }
}
