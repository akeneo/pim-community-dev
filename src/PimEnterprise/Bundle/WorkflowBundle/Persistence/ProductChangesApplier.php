<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Persistence;

use Pim\Bundle\CatalogBundle\Model\AbstractProduct;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * Applies product changes
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProductChangesApplier
{
    protected $denormalizer;

    public function __construct(DenormalizerInterface $denormalizer)
    {
        $this->denormalizer = $denormalizer;
    }

    public function apply(AbstractProduct $product, $key, $data)
    {
        $key = $this->parseKey($key);
        if (null === $value = $product->getValue($key['code'], $key['locale'], $key['scope'])) {
            return;
        }

        $this->denormalizer->denormalize($data, $value->getAttribute()->getAttributeType(), null, [
            'instance' => $value
        ]);
    }

    protected function parseKey($key)
    {
        $parts = explode('-', $key);
        $code = $locale = $scope = null;

        if (isset($parts[0])) {
            $code = $parts[0];
        }

        if (isset($parts[1])) {
            $locale = $parts[1];
        }

        if (isset($parts[2])) {
            $scope = $parts[2];
        }

        return [
            'code' => $code,
            'locale' => $locale,
            'scope' => $scope,
        ];
    }
}
