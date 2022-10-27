<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter;

use Akeneo\Pim\Enrichment\Component\Product\Comparator\ComparatorRegistry;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\ProductNormalizer;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Filter product's association to have only updated or new values
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAssociationFilter implements FilterInterface
{
    /** @var NormalizerInterface */
    protected $associationsNormalizer;

    /** @var NormalizerInterface */
    protected $quantifiedAssociationsNormalizer;

    /** @var ComparatorRegistry */
    protected $comparatorRegistry;

    public function __construct(
        NormalizerInterface $associationsNormalizer,
        NormalizerInterface $quantifiedAssociationsNormalizer,
        ComparatorRegistry $comparatorRegistry,
        private Connection $connection,
    ) {
        $this->associationsNormalizer = $associationsNormalizer;
        $this->quantifiedAssociationsNormalizer = $quantifiedAssociationsNormalizer;
        $this->comparatorRegistry = $comparatorRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function filter(EntityWithValuesInterface $product, array $newValues): array
    {
        $originalAssociations = $this->associationsNormalizer->normalize($product, 'standard');
        $originalQuantifiedAssociations = $this->quantifiedAssociationsNormalizer->normalize($product, 'standard');

        if (
            !$this->hasNewAssociations($newValues) &&
            !$this->hasNewQuantifiedAssociations($newValues) &&
            empty($originalAssociations) &&
            empty($originalQuantifiedAssociations)
        ) {
            return [];
        }

        if (isset($newValues['associations'])) {
            $isImportingByUuids = null;
            foreach ($newValues['associations'] as $associationCode => $associationsByCode) {
                if (\array_key_exists('products', $associationsByCode)) {
                    if (true === $isImportingByUuids) {
                        throw new \LogicException('You can not filter by uuid and by identifiers');
                    }
                    $isImportingByUuids = false;
                }
                if (\array_key_exists('product_uuids', $associationsByCode)) {
                    if (false === $isImportingByUuids) {
                        throw new \LogicException('You can not filter by uuid and by identifiers');
                    }
                    $isImportingByUuids = true;
                }
            }
            if ($isImportingByUuids) {
                $newValues = $this->transform($newValues);
                $originalAssociations = $this->associationsNormalizer->normalize($product, 'standard', ['with_association_uuids' => true]);
            }
        }


        $result = [];

        if (!isset($newValues[ProductNormalizer::FIELD_ASSOCIATIONS])) {
            return $result;
        }

        foreach ($newValues[ProductNormalizer::FIELD_ASSOCIATIONS] as $type => $field) {
            foreach ($field as $key => $association) {
                $data = $this->compareAssociations($originalAssociations, $association, $type, $key);

                if (null !== $data) {
                    $result[ProductNormalizer::FIELD_ASSOCIATIONS][$type][$key] = $data;
                }
            }
        }

        if (!isset($newValues[ProductNormalizer::FIELD_QUANTIFIED_ASSOCIATIONS])) {
            return $result;
        }

        foreach ($newValues[ProductNormalizer::FIELD_QUANTIFIED_ASSOCIATIONS] as $type => $field) {
            foreach ($field as $key => $association) {
                $data = $this->compareQuantifiedAssociations($originalQuantifiedAssociations, $association, $type, $key);

                if (null !== $data) {
                    $result[ProductNormalizer::FIELD_QUANTIFIED_ASSOCIATIONS][$type][$key] = $data;
                }
            }
        }

        return $result;
    }

    /**
     * Has association(s) in new values?
     */
    protected function hasNewAssociations(array $convertedItem): bool
    {
        if (!isset($convertedItem['associations'])) {
            return false;
        }

        foreach ($convertedItem['associations'] as $association) {
            if (!empty($association['products']) || !empty($association['groups']) || !empty($association['product_models']) || !empty($association['product_uuids'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Has quantified association(s) in new values?
     */
    protected function hasNewQuantifiedAssociations(array $convertedItem): bool
    {
        if (!isset($convertedItem['quantified_associations'])) {
            return false;
        }

        foreach ($convertedItem['quantified_associations'] as $association) {
            if (!empty($association['products']) || !empty($association['product_models'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Compare product's associations
     *
     * @throws \LogicException
     */
    protected function compareAssociations(array $originalAssociations, array $newAssociations, string $type, string $key): ?array
    {
        $comparator = $this->comparatorRegistry->getFieldComparator(ProductNormalizer::FIELD_ASSOCIATIONS);

        return $comparator->compare($newAssociations, $this->getOriginalAssociation($originalAssociations, $type, $key));
    }

    /**
     * Compare product's quantified associations
     *
     * @throws \LogicException
     */
    protected function compareQuantifiedAssociations(array $originalQuantifiedAssociations, array $newQuantifiedAssociations, string $type, string $key): ?array
    {
        $comparator = $this->comparatorRegistry->getFieldComparator(ProductNormalizer::FIELD_QUANTIFIED_ASSOCIATIONS);

        return $comparator->compare($newQuantifiedAssociations, $this->getOriginalAssociation($originalQuantifiedAssociations, $type, $key));
    }

    protected function getOriginalAssociation(array $originalAssociations, string $type, string $key): array
    {
        return !isset($originalAssociations[$type][$key]) ? [] : $originalAssociations[$type][$key];
    }

    private function transform(array $newValues)
    {
        $result = [];

        if (isset($newValues['associations'])) {
            $result['associations'] = [];
            foreach ($newValues['associations'] as $associationCode => $associationsByCode) {
                $result['associations'][$associationCode] = [];
                foreach ($associationsByCode as $associationType => $associationsByType) {
                    if ($associationType === 'products') {
                        $result['associations'][$associationCode]['product_uuids'] = array_map(fn (string $identifier): string => $this->getProductUuid($identifier), $associationsByType);
                    } else {
                        $result['associations'][$associationCode][$associationType] = $associationsByType;
                    }
                }
            }
        }
        if (isset($newValues['quantified_associations'])) {
            $result['quantified_associations'] = $newValues['quantified_associations'];
        }

        return $result;
    }

    // TODO Do this better
    private function getProductUuid(string $identifier): ?string
    {
        return $this->connection->executeQuery(
            'SELECT BIN_TO_UUID(uuid) AS uuid FROM pim_catalog_product WHERE identifier = :identifier',
            ['identifier' => $identifier]
        )->fetchOne() ?: null;
    }
}
