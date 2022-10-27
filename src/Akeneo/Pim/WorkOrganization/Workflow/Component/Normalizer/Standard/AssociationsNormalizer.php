<?php

namespace Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\Standard;

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\GetAssociatedProductCodesByPublishedProduct;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webmozart\Assert\Assert;

class AssociationsNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    public function __construct(
        private GetAssociatedProductCodesByPublishedProduct $getAssociatedProductCodesByPublishedProduct
    ) {
    }

    /**
     * {@inheritdoc}
     *
     * @param PublishedProductInterface $publishedProduct
     */
    public function normalize($publishedProduct, $format = null, array $context = [])
    {
        $ancestorProducts = $this->getAncestorProducts($publishedProduct);
        $data = $this->normalizeAssociations($ancestorProducts);

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return get_class($data) === 'Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProduct'
            && 'standard' === $format;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    /**
     * @return array|EntityWithFamilyVariantInterface[]
     */
    private function getAncestorProducts(EntityWithFamilyVariantInterface $publishedProduct): array
    {
        $parent = $publishedProduct->getParent();

        if (null === $parent) {
            return [$publishedProduct];
        }

        return array_merge($this->getAncestorProducts($parent), [$publishedProduct]);
    }

    /**
     * @param EntityWithAssociationsInterface[] $associationAwareEntities
     */
    private function normalizeAssociations(array $associationAwareEntities): array
    {
        $data = [];

        foreach ($associationAwareEntities as $associationAwareEntity) {
            Assert::implementsInterface($associationAwareEntity, EntityWithAssociationsInterface::class);
            Assert::implementsInterface($associationAwareEntity, EntityWithValuesInterface::class);

            foreach ($associationAwareEntity->getAssociations() as $association) {
                $code = $association->getAssociationType()->getCode();

                $data[$code]['groups'] = $data[$code]['groups'] ?? [];
                foreach ($association->getGroups() as $group) {
                    $data[$code]['groups'][] = $group->getCode();
                }

                $data[$code]['product_uuids'] = $data[$code]['product_uuids'] ?? [];
                if ($associationAwareEntity instanceof ProductModelInterface) {
                    foreach ($association->getProducts() as $product) {
                        $data[$code]['product_uuids'][] = $product->getUuid()->toString();
                    }
                } else {
                    $data[$code]['product_uuids'] = array_merge(
                        $data[$code]['product_uuids'],
                        $this->getAssociatedProductCodesByPublishedProduct->getUuids(
                            $associationAwareEntity->getId(),
                            $association
                        )
                    );
                }

                $data[$code]['product_models'] = $data[$code]['product_models'] ?? [];
                foreach ($association->getProductModels() as $productModel) {
                    $data[$code]['product_models'][] = $productModel->getCode();
                }
            }
        }

        $data = array_map(function ($association) {
            $association['product_uuids'] = array_values(array_unique($association['product_uuids']));
            return $association;
        }, $data);

        ksort($data);

        return $data;
    }
}
