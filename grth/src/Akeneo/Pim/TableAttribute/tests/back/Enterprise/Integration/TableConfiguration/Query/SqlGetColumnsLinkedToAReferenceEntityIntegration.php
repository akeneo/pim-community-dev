<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\TableAttribute\Enterprise\Integration\TableConfiguration\Query;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Query\GetColumnsLinkedToAReferenceEntity;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ReferenceEntityColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
use Akeneo\ReferenceEntity\Application\ReferenceEntity\CreateReferenceEntity\CreateReferenceEntityCommand;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

class SqlGetColumnsLinkedToAReferenceEntityIntegration extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->createReferenceEntity('brand');
        $this->createReferenceEntity('city');
        $this->createTableAttribute('packaging', [
            ['data_type' => SelectColumn::DATATYPE, 'code' => 'dimension', 'options' => [
                ['code' => 'width', 'labels' => ['en_US' => 'Width']],
                ['code' => 'height', 'labels' => ['en_US' => 'Height']],
                ['code' => 'depth'],
            ]],
            ['data_type' => ReferenceEntityColumn::DATATYPE, 'code' => 'record', 'reference_entity_identifier' => 'brand'],
            ['data_type' => ReferenceEntityColumn::DATATYPE, 'code' => 'name', 'reference_entity_identifier' => 'city'],
        ]);
        $this->createTableAttribute('origin', [
            ['data_type' => SelectColumn::DATATYPE, 'code' => 'element', 'options' => []],
            ['data_type' => ReferenceEntityColumn::DATATYPE, 'code' => 'name', 'reference_entity_identifier' => 'city'],
        ]);
    }

    public function test_it_returns_all_reference_entity_columns_by_reference_entity_identifier()
    {
        $getColumnsLinkedToAReferenceEntity = $this->get(GetColumnsLinkedToAReferenceEntity::class);

        $this->assertEquals(
            [
                ['attribute_code' => 'packaging', 'column_code' => 'record'],
            ],
            $getColumnsLinkedToAReferenceEntity->forIdentifier('brand')
        );

        $this->assertEquals(
            [
                ['attribute_code' => 'origin', 'column_code' => 'name'],
                ['attribute_code' => 'packaging', 'column_code' => 'name'],
            ],
            $getColumnsLinkedToAReferenceEntity->forIdentifier('city')
        );

        $this->assertEquals(
            [],
            $getColumnsLinkedToAReferenceEntity->forIdentifier('unknown')
        );
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createTableAttribute(string $attributeCode, array $tableConfig): void
    {
        $attribute = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update($attribute, [
            'code' => $attributeCode,
            'type' => AttributeTypes::TABLE,
            'group' => 'other',
            'localizable' => false,
            'scopable' => false,
            'table_configuration' => $tableConfig,
        ]);
        $violations = $this->get('validator')->validate($attribute);
        self::assertCount(0, $violations, (string)$violations);
        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }

    private function createReferenceEntity(string $referenceEntityIdentifier): void
    {
        $createCommand = new CreateReferenceEntityCommand($referenceEntityIdentifier, []);

        $violations = $this->get('validator')->validate($createCommand);
        self::assertCount(0, $violations, (string)$violations);

        $handler = $this->get('akeneo_referenceentity.application.reference_entity.create_reference_entity_handler');
        ($handler)($createCommand);
    }
}
