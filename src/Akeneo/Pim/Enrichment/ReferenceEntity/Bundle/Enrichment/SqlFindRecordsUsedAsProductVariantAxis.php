<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\ReferenceEntity\Bundle\Enrichment;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\ProductAndProductModelQueryBuilderFactory;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\AttributeType\ReferenceEntityCollectionType;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\AttributeType\ReferenceEntityType;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Query\FindRecordsUsedAsProductVariantAxisInterface;
use Akeneo\Pim\Structure\Component\FamilyVariant\Query\FamilyVariantsByAttributeAxesInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;

/**
 * @copyright 2020 Akeneo SAS (https://www.akeneo.com)
 */
class SqlFindRecordsUsedAsProductVariantAxis implements FindRecordsUsedAsProductVariantAxisInterface
{
    private Connection $sqlConnection;
    private FamilyVariantsByAttributeAxesInterface $familyVariantsByAttributeAxes;
    private ProductAndProductModelQueryBuilderFactory $pqbFactory;

    public function __construct(
        Connection $sqlConnection,
        FamilyVariantsByAttributeAxesInterface $familyVariantsByAttributeAxes,
        ProductAndProductModelQueryBuilderFactory $pqbFactory
    ) {
        $this->sqlConnection = $sqlConnection;
        $this->familyVariantsByAttributeAxes = $familyVariantsByAttributeAxes;
        $this->pqbFactory = $pqbFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function areUsed(array $recordCodes, string $referenceEntityIdentifier): bool
    {
        $attributeCodes = $this->findProductAttributeCodesLinkedToReferenceEntity($referenceEntityIdentifier);

        foreach ($attributeCodes as $attributeCode) {
            $familyVariantIdentifiers = $this->familyVariantsByAttributeAxes->findIdentifiers([$attributeCode]);

            if (empty($familyVariantIdentifiers)) {
                continue;
            }

            if (
                0 !== $this->countEntitiesUsingRecordsAsProductVariantAxis(
                    $familyVariantIdentifiers,
                    $attributeCode,
                    $recordCodes
                )
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsedCodes(array $recordCodes, string $referenceEntityIdentifier): array
    {
        $attributeCodes = $this->findProductAttributeCodesLinkedToReferenceEntity($referenceEntityIdentifier);
        $recordCodesUsedAsAxis = [];

        foreach ($attributeCodes as $attributeCode) {
            $familyVariantIdentifiers = $this->familyVariantsByAttributeAxes->findIdentifiers([$attributeCode]);

            if (empty($familyVariantIdentifiers)) {
                continue;
            }

            foreach ($recordCodes as $recordCode) {
                if (0 !== $this->countEntitiesUsingRecordsAsProductVariantAxis(
                    $familyVariantIdentifiers,
                    $attributeCode,
                    [$recordCode]
                )) {
                    $recordCodesUsedAsAxis[] = $recordCode;
                }
            }
        }

        return $recordCodesUsedAsAxis;
    }

    private function findProductAttributeCodesLinkedToReferenceEntity(
        string $referenceEntityIdentifier
    ): array {
        $query = <<<SQL
        SELECT code, properties
        FROM pim_catalog_attribute
        WHERE attribute_type IN (:attribute_types)
SQL;
        $statement = $this->sqlConnection->executeQuery(
            $query,
            [
                'attribute_types' => [
                    ReferenceEntityCollectionType::REFERENCE_ENTITY_COLLECTION,
                    ReferenceEntityType::REFERENCE_ENTITY,
                ],
            ],
            [
                'attribute_types' => Connection::PARAM_STR_ARRAY,
            ],
        );

        $results = $statement->fetchAllAssociative();
        $statement->free();

        $platform = $this->sqlConnection->getDatabasePlatform();

        $results = array_filter($results, function ($result) use ($platform, $referenceEntityIdentifier) {
            $properties = Type::getType(Types::ARRAY)->convertToPhpValue($result['properties'], $platform);

            return $properties['reference_data_name'] === $referenceEntityIdentifier;
        });

        return array_column($results, 'code');
    }

    /**
     * @param string[] $familyVariantIdentifiers
     * @param string[] $recordCodes
     */
    private function countEntitiesUsingRecordsAsProductVariantAxis(
        array $familyVariantIdentifiers,
        string $attributeCode,
        array $recordCodes
    ): int {
        $pqb = $this->pqbFactory->create([
            'filters' => [
                [
                    'field' => 'family_variant',
                    'operator' => 'IN',
                    'value' => $familyVariantIdentifiers,
                ],
                [
                    'field' => $attributeCode,
                    'operator' => 'IN',
                    'value' => $recordCodes,
                ],
            ],
        ]);

        return $pqb->execute()->count();
    }
}
