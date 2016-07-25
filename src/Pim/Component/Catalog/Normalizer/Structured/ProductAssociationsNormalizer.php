<?php

namespace Pim\Component\Catalog\Normalizer\Structured;

use Pim\Component\Catalog\Model\ProductInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * A normalizer to transform product associations into an array
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAssociationsNormalizer implements NormalizerInterface
{
    /** @var string[] $supportedFormats */
    protected $supportedFormats = ['json', 'xml'];

    /**
     * {@inheritdoc}
     *
     * @param ProductInterface $product
     */
    public function normalize($product, $format = null, array $context = [])
    {
        $data = [];

        foreach ($product->getAssociations() as $association) {
            $code = $association->getAssociationType()->getCode();
            $data[$code] = ['groups' => [], 'products' => []];

            foreach ($association->getGroups() as $group) {
                $data[$code]['groups'][] = $group->getCode();
            }

            foreach ($association->getProducts() as $product) {
                $data[$code]['products'][] = $product->getReference();
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
        return $data instanceof ProductInterface && in_array($format, $this->supportedFormats);
    }
}
