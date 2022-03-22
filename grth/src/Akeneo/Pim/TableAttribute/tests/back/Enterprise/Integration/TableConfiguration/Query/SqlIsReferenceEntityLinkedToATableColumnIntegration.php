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
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Query\IsReferenceEntityLinkedToATableColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ReferenceEntityColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
use Akeneo\ReferenceEntity\Application\ReferenceEntity\CreateReferenceEntity\CreateReferenceEntityCommand;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

class SqlIsReferenceEntityLinkedToATableColumnIntegration extends TestCase
{
    /**
     * @test
     */
    public function itSaysIfAReferenceEntityIsLinkedToATableColumn(): void
    {
        $service = $this->get(IsReferenceEntityLinkedToATableColumn::class);

        self::assertFalse($service->forIdentifier('toto'));
        self::assertTrue($service->forIdentifier('brand'));
        self::assertTrue($service->forIdentifier('BRAnd'));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->createReferenceEntity('brand');

        $this->createTableAttribute('packaging', [
            ['data_type' => SelectColumn::DATATYPE, 'code' => 'dimension', 'options' => [
                ['code' => 'width', 'labels' => ['en_US' => 'Width']],
                ['code' => 'height', 'labels' => ['en_US' => 'Height']],
                ['code' => 'depth'],
            ]],
            ['data_type' => ReferenceEntityColumn::DATATYPE, 'code' => 'record', 'reference_entity_identifier' => 'brand'],
        ]);
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
