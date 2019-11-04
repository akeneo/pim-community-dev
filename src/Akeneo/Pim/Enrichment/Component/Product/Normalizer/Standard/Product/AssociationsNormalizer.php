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
class AssociationsNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /** @var GetAssociatedProductCodesByProduct */
    private $getAssociatedProductCodeByProduct;

    public function __construct(GetAssociatedProductCodesByProduct $getAssociatedProductCodeByProduct)
    {
        $this->getAssociatedProductCodeByProduct = $getAssociatedProductCodeByProduct;
    }

    /**
     * {@inheritdoc}
     *
     * @param EntityWithAssociationsInterface $associationAwareEntity
     */
    public function normalize($associationAwareEntity, $format = null, array $context = [])
    {
        $ancestorProducts = $this->getAncestorProducts($associationAwareEntity);
        $data = $this->normalizeAssociations($ancestorProducts);

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
     * @param EntityWithAssociationsInterface[] $associationAwareEntities
     *
     * @return array
     */
    private function normalizeAssociations(array $associationAwareEntities)
    {
        $data = [];

        foreach ($associationAwareEntities as $associationAwareEntity) {
            foreach ($associationAwareEntity->getAssociations() as $association) {
                $code = $association->getAssociationType()->getCode();

                $data[$code]['groups'] = $data[$code]['groups'] ?? [];
                foreach ($association->getGroups() as $group) {
                    $data[$code]['groups'][] = $group->getCode();
                }

                $data[$code]['products'] = $data[$code]['products'] ?? [];
                if ($associationAwareEntity instanceof ProductModelInterface) {
                    foreach ($association->getProducts() as $product) {
                        $data[$code]['products'][] = $product->getReference();
                    }
                } else {
                    $data[$code]['products'] = array_merge($data[$code]['products'], $this->getAssociatedProductCodeByProduct->getCodes(
                        $associationAwareEntity->getId(),
                        $association
                    ));
                }

                $data[$code]['product_models'] = $data[$code]['product_models'] ?? [];
                foreach ($association->getProductModels() as $productModel) {
                    $data[$code]['product_models'][] = $productModel->getCode();
                }
            }
        }

        $data = array_map(function ($association) {
            $association['products'] = array_unique($association['products']);
            return $association;
        }, $data);

        ksort($data);

        return $data;
    }
}
