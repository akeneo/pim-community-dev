<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\Comparator\Filter;

use Akeneo\Component\StorageUtils\Exception\UnknownPropertyException;
use Pim\Component\Catalog\Comparator\ComparatorRegistry;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Filter product's fields to have only updated or new values
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductFieldFilter implements ProductFilterInterface
{
    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var ComparatorRegistry */
    protected $comparatorRegistry;

    /** @var array */
    protected $productFields;

    /**
     * @param NormalizerInterface $normalizer
     * @param ComparatorRegistry  $comparatorRegistry
     * @param array               $productFields
     */
    public function __construct(
        NormalizerInterface $normalizer,
        ComparatorRegistry $comparatorRegistry,
        array $productFields
    ) {
        $this->normalizer = $normalizer;
        $this->comparatorRegistry = $comparatorRegistry;
        $this->productFields = $productFields;
    }

    /**
     * {@inheritdoc}
     */
    public function filter(ProductInterface $product, array $newFields)
    {
        $originalProduct = $this->normalizer->normalize($product, 'standard');
        $result = [];

        foreach ($newFields as $field => $value) {
            if (!in_array($field, $this->productFields)) {
                throw new \LogicException(sprintf('Cannot filter value of field "%s"', $field));
            }

            $comparator = $this->comparatorRegistry->getFieldComparator($field);
            $originalData = !isset($originalProduct[$field]) ? null : $originalProduct[$field];
            $diff = $comparator->compare($value, $originalData);

            if (null !== $diff) {
                $result[$field] = $diff;
            }
        }

        return $result;
    }
}
