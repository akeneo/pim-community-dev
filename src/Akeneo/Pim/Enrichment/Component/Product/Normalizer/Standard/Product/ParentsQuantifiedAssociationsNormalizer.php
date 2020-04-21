<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query\Product\MapProduct;
use Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query\Product\MapProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize associations into an array
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ParentsQuantifiedAssociationsNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    private $mapProduct;
    private $mapProductModel;

    public function __construct(
        MapProduct $mapProduct,
        MapProductModel $mapProductModel
    ) {
        $this->mapProduct = $mapProduct;
        $this->mapProductModel = $mapProductModel;
    }

    /**
     * {@inheritdoc}
     *
     * @param EntityWithAssociationsInterface $associationAwareEntity
     */
    public function normalize($associationAwareEntity, $format = null, array $context = [])
    {
        $ancestorProducts = $this->getAncestorProducts($associationAwareEntity->getParent());
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
        $productIdentifiers = $this->getProductIdentifiers($associationAwareEntities);
        $productModelCodes = $this->getProductModelCodes($associationAwareEntities);

        $mergedQuantifiedAssociations = [];
        foreach ($associationAwareEntities as $associationAwareEntity) {
            $quantifiedAssociations = $associationAwareEntity->getQuantifiedAssociationsWithIdentifiersAndCodes(
                $productIdentifiers,
                $productModelCodes
            );
            foreach ($quantifiedAssociations as $associationTypeCode => $quantifiedAssociation) {
                if (!isset($mergedQuantifiedAssociations[$associationTypeCode])) {
                    $mergedQuantifiedAssociations[$associationTypeCode] = ['products' => [], 'product_models' => []];
                }

                foreach ($quantifiedAssociation['products'] as $quantifiedProduct) {
                    $mergedQuantifiedAssociations[$associationTypeCode]['products'] = array_merge(
                        array_filter(
                            $mergedQuantifiedAssociations[$associationTypeCode]['products'],
                            function ($mergedQuantifiedAssociation) use ($quantifiedProduct) {
                                return $quantifiedProduct['identifier'] !== $mergedQuantifiedAssociation['identifier'];
                            }
                        ),
                        [$quantifiedProduct]
                    );
                }
                foreach ($quantifiedAssociation['product_models'] as $quantifiedProductModel) {
                    $mergedQuantifiedAssociations[$associationTypeCode]['product_models'] = array_merge(
                        array_filter(
                            $mergedQuantifiedAssociations[$associationTypeCode]['product_models'],
                            function ($mergedQuantifiedAssociation) use ($quantifiedProductModel) {
                                return $quantifiedProductModel['code'] !== $mergedQuantifiedAssociation['code'];
                            }
                        ),
                        [$quantifiedProductModel]
                    );
                }
            }
        }

        return $mergedQuantifiedAssociations;
    }

    private function getProductIdentifiers(array $associationAwareEntities)
    {
        $productIds = array_reduce($associationAwareEntities, function (array $carry, EntityWithAssociationsInterface $product) {
            return array_merge($carry, $product->getAllLinkedProductIds());
        }, []);

        return $this->mapProduct->forIds($productIds);
    }

    private function getProductModelCodes(array $associationAwareEntities)
    {
        $productModelIds = array_reduce($associationAwareEntities, function (array $carry, EntityWithAssociationsInterface $product) {
            return array_merge($carry, $product->getAllLinkedProductModelIds());
        }, []);

        return $this->mapProductModel->forIds($productModelIds);
    }
}
