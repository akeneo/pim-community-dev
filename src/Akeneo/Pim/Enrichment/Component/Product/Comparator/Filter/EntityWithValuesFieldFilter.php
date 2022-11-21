<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter;

use Akeneo\Pim\Enrichment\Component\Product\Comparator\ComparatorRegistry;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Filter entity with values fields to have only updated or new values
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EntityWithValuesFieldFilter implements FilterInterface
{
    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var ComparatorRegistry */
    protected $comparatorRegistry;

    /** @var array */
    protected $entityFields;

    /**
     * @param NormalizerInterface $normalizer
     * @param ComparatorRegistry  $comparatorRegistry
     * @param array               $entityFields
     */
    public function __construct(
        NormalizerInterface $normalizer,
        ComparatorRegistry $comparatorRegistry,
        array $entityFields
    ) {
        $this->normalizer = $normalizer;
        $this->comparatorRegistry = $comparatorRegistry;
        $this->entityFields = $entityFields;
    }

    /**
     * {@inheritdoc}
     */
    public function filter(EntityWithValuesInterface $entity, array $newFields): array
    {
        $originalEntity = $this->normalizer->normalize($entity, 'standard');
        $result = [];

        foreach ($newFields as $field => $value) {
            if (!in_array($field, $this->entityFields)) {
                throw new \LogicException(sprintf('Cannot filter value of field "%s"', $field));
            }

            $comparator = $this->comparatorRegistry->getFieldComparator($field);
            $originalData = !isset($originalEntity[$field]) ? null : $originalEntity[$field];
            $diff = $comparator->compare($value, $originalData);

            if (null !== $diff) {
                $result[$field] = $diff;
            }
        }

        return $result;
    }
}
