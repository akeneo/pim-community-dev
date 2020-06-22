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
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Query\RecordIsUsedAsProductVariantAxisInterface;
use Akeneo\Pim\Structure\Component\FamilyVariant\Query\FamilyVariantsByAttributeAxesInterface;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;

/**
 * @copyright 2020 Akeneo SAS (https://www.akeneo.com)
 */
class SqlRecordIsUsedAsProductVariantAxis implements RecordIsUsedAsProductVariantAxisInterface
{
    /** @var Connection */
    private $sqlConnection;

    /** @var FamilyVariantsByAttributeAxesInterface */
    private $familyVariantsByAttributeAxes;

    /** @var ProductAndProductModelQueryBuilderFactory */
    private $pqbFactory;

    public function __construct(
        Connection $sqlConnection,
        FamilyVariantsByAttributeAxesInterface $familyVariantsByAttributeAxes,
        ProductAndProductModelQueryBuilderFactory $pqbFactory
    ) {
        $this->sqlConnection = $sqlConnection;
        $this->familyVariantsByAttributeAxes = $familyVariantsByAttributeAxes;
        $this->pqbFactory = $pqbFactory;
    }

    public function execute(RecordCode $recordCode, ReferenceEntityIdentifier $referenceEntityIdentifier): bool
    {
        $attributeCodes = $this->findProductAttributeCodesLinkedToReferenceEntity($referenceEntityIdentifier);

        foreach ($attributeCodes as $attributeCode) {
            $familyVariantsIdentifiers = $this->familyVariantsByAttributeAxes->findIdentifiers([$attributeCode]);

            if (empty($familyVariantsIdentifiers)) {
                continue;
            }

            if (0 !== $this->countEntitiesUsingRecordAsProductVariantAxis(
                    $familyVariantsIdentifiers,
                    $attributeCode,
                    $recordCode)
            ) {
                return true;
            }
        }

        return false;
    }

    private function findProductAttributeCodesLinkedToReferenceEntity(
        ReferenceEntityIdentifier $referenceEntityIdentifier
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
            ]
        );

        $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
        $statement->closeCursor();

        $platform = $this->sqlConnection->getDatabasePlatform();

        $results = array_filter($results, function ($result) use ($platform, $referenceEntityIdentifier) {
            $properties = Type::getType(Types::ARRAY)->convertToPhpValue($result['properties'], $platform);

            return $properties['reference_data_name'] === $referenceEntityIdentifier->normalize();
        });

        $results = array_map(function ($result) {
            return $result['code'];
        }, $results);

        return $results;
    }

    private function countEntitiesUsingRecordAsProductVariantAxis(
        array $familyVariantsIdentifier,
        string $attributeCode,
        RecordCode $recordCode
    ): int {
        $pqb = $this->pqbFactory->create([
            'filters' => [
                [
                    'field' => 'family_variant',
                    'operator' => 'IN',
                    'value' => $familyVariantsIdentifier,
                ],
                [
                    'field' => $attributeCode,
                    'operator' => 'IN',
                    'value' => [(string)$recordCode],
                ],
            ],
        ]);

        return $pqb->execute()->count();
    }
}
