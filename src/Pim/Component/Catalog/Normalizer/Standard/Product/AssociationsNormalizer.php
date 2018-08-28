<?php

namespace Pim\Component\Catalog\Normalizer\Standard\Product;

use Pim\Component\Catalog\Model\EntityWithAssociationsInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Query\AssociatedProduct\GetAssociatedProductCodesByProduct;
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
    /** @var GetAssociatedProductCodesByProduct */
    private $getAssociatedProductCodeByProduct;

    // TODO: remove null on master
    public function __construct(GetAssociatedProductCodesByProduct $getAssociatedProductCodeByProduct = null)
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
        if (null !== $this->getAssociatedProductCodeByProduct) {
            $data = $this->normalizeAssociations($associationAwareEntity);
        } else { // TODO: remove it on master
            $data = $this->legacyNormalizeAssociations($associationAwareEntity);
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof EntityWithAssociationsInterface && 'standard' === $format;
    }

    /**
     * @param EntityWithAssociationsInterface $associationAwareEntity
     *
     * @return array
     */
    private function normalizeAssociations(EntityWithAssociationsInterface $associationAwareEntity)
    {
        $data = [];

        foreach ($associationAwareEntity->getAllAssociations() as $association) {
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

        $data = array_map(function ($association) {
            $association['products'] = array_unique($association['products']);
            return $association;
        }, $data);

        ksort($data);

        return $data;
    }

    /**
     * TODO: remove it and keep only normalizeAssociations() on master
     *
     * @param EntityWithAssociationsInterface $associationAwareEntity
     *
     * @return array
     */
    private function legacyNormalizeAssociations(EntityWithAssociationsInterface $associationAwareEntity)
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
}
