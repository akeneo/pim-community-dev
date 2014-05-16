<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Persistence;

use Pim\Bundle\CatalogBundle\Model\AbstractProduct;
use PimEnterprise\Bundle\WorkflowBundle\Serialization\FlatProductValueDenormalizer;

/**
 * Applies product changes
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProductChangesApplier
{
    /** @var FlatProductValueDenormalizer */
    protected $denormalizer;

    /**
     * @param FlatProductValueDenormalizer $denormalizer
     */
    public function __construct(FlatProductValueDenormalizer $denormalizer)
    {
        $this->denormalizer = $denormalizer;
    }

    /**
     * @param AbstractProduct $product
     * @param string          $key
     * @param mixed           $data
     *
     * @return null
     */
    public function apply(AbstractProduct $product, $key, $data)
    {
        $key = $this->parseKey($key);
        if (null === $value = $product->getValue($key['code'], $key['locale'], $key['scope'])) {
            return;
        }

        $this->denormalizer->denormalize($data, get_class($value), 'csv', ['instance' => $value]);
    }

    /**
     * @param string $key
     *
     * @return array
     */
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
