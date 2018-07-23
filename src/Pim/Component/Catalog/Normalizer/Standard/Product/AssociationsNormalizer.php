<?php

namespace Pim\Component\Catalog\Normalizer\Standard\Product;

use Pim\Component\Catalog\Model\ProductInterface;
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
     */
    public function normalize($product, $format = null, array $context = [])
    {
        if (null !== $this->getAssociatedProductCodeByProduct) {
            $data = $this->normalizeAssociations($product);
        } else { // TODO: remove it on master
            $data = $this->legacyNormalizeAssociations($product);
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductInterface && 'standard' === $format;
    }

    /**
     * @param ProductInterface $product
     *
     * @return array
     */
    private function normalizeAssociations(ProductInterface $product)
    {
        $data = [];

        foreach ($product->getAssociations() as $association) {
            if ($association->getGroups()->count() > 0 || $association->getProducts()->count() > 0) {
                $code = $association->getAssociationType()->getCode();

                $data[$code]['groups'] = [];
                foreach ($association->getGroups() as $group) {
                    $data[$code]['groups'][] = $group->getCode();
                }

                $data[$code]['products'] = $this->getAssociatedProductCodeByProduct->getCodes(
                    $product->getId(),
                    $association->getAssociationType()->getId()
                );
            }
        }

        ksort($data);

        return $data;
    }

    /**
     * TODO: remove it and keep only normalizeAssociations() on master
     *
     * @param ProductInterface $product
     *
     * @return array
     */
    private function legacyNormalizeAssociations(ProductInterface $product)
    {
        $data = [];

        foreach ($product->getAssociations() as $association) {
            $code = $association->getAssociationType()->getCode();
            $data[$code]['groups'] = [];
            foreach ($association->getGroups() as $group) {
                $data[$code]['groups'][] = $group->getCode();
            }

            $data[$code]['products'] = [];
            foreach ($association->getProducts() as $product) {
                $data[$code]['products'][] = $product->getReference();
            }
        }

        ksort($data);

        return $data;
    }
}
