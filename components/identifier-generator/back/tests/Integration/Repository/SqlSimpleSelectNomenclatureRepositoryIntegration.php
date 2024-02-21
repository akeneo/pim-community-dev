<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Integration\Repository;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\NomenclatureDefinition;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\SimpleSelectNomenclatureRepository;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlSimpleSelectNomenclatureRepositoryIntegration extends TestCase
{
    private SimpleSelectNomenclatureRepository $simpleSelectNomenclatureRepository;
    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->simpleSelectNomenclatureRepository = $this->get(SimpleSelectNomenclatureRepository::class);
        $this->connection = $this->get('database_connection');

        $this->createAttribute([
            'code' => 'size',
            'type' => 'pim_catalog_simpleselect',
            'group' => 'other',
            'localizable' => false,
            'scopable' => false,
            'labels' => [],
        ]);

        $this->createAttributeOption([
            'code' => 's',
            'attribute' => 'size',
            'labels' => [],
        ]);
        $this->createAttributeOption([
            'code' => 'm',
            'attribute' => 'size',
            'labels' => [],
        ]);

        $this->createAttributeOption([
            'code' => 'l',
            'attribute' => 'size',
            'labels' => [],
        ]);
    }

    /** @test */
    public function it_saves_simple_select_nomenclatures(): void
    {
        $simpleSelectNomenclatureDefinition = new NomenclatureDefinition(
            '=',
            5,
            false,
            ['s' => 'small', 'm' => 'mediu']
        );
        $this->simpleSelectNomenclatureRepository->update(
            'size',
            $simpleSelectNomenclatureDefinition
        );

        $this->assertSameNomenclatureDefinition($simpleSelectNomenclatureDefinition, $this->simpleSelectNomenclatureRepository->get('size'));
    }

    /** @test */
    public function it_updates_a_simple_select_nomenclature(): void
    {
        $simpleSelectNomenclatureDefinition = new NomenclatureDefinition(
            '=',
            5,
            false,
            ['s' => 'small', 'm' => 'mediu']
        );
        $this->simpleSelectNomenclatureRepository->update(
            'size',
            $simpleSelectNomenclatureDefinition
        );

        $updateSelectNomenclatureDefinition = new NomenclatureDefinition(
            '<=',
            5,
            false,
            ['s' => null, 'l' => 'largo']
        );
        $this->simpleSelectNomenclatureRepository->update(
            'size',
            $updateSelectNomenclatureDefinition
        );

        $expected = new NomenclatureDefinition(
            '<=',
            5,
            false,
            ['m' => 'mediu', 'l' => 'largo']
        );

        $this->assertSameNomenclatureDefinition($expected, $this->simpleSelectNomenclatureRepository->get('size'));
    }

    /** @test */
    public function it_saves_and_updates_a_simple_select_nomenclature_with_different_case(): void
    {
        $this->simpleSelectNomenclatureRepository->update(
            'sIzE',
            new NomenclatureDefinition(
                '=',
                5,
                false,
                ['s' => 'small', 'm' => 'mediu']
            )
        );

        $this->assertSameNomenclatureDefinition(
            new NomenclatureDefinition(
                '=',
                5,
                false,
                ['s' => 'small', 'm' => 'mediu']
            ),
            $this->simpleSelectNomenclatureRepository->get('SIZE')
        );
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog(['identifier_generator']);
    }

    private function assertSameNomenclatureDefinition(NomenclatureDefinition $expected, NomenclatureDefinition $result): void
    {
        Assert::assertEquals($expected->operator(), $result->operator());
        Assert::assertEquals($expected->value(), $result->value());
        Assert::assertEquals($expected->generateIfEmpty(), $result->generateIfEmpty());
        Assert::assertEqualsCanonicalizing($expected->values(), $result->values());
    }

    /**
     * @param array $data
     *
     * @return AttributeInterface
     */
    protected function createAttribute(array $data = []): AttributeInterface
    {
        $attribute = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update($attribute, $data);
        $constraintList = $this->get('validator')->validate($attribute);
        $this->assertEquals(0, $constraintList->count());
        $this->get('pim_catalog.saver.attribute')->save($attribute);

        return $attribute;
    }

    /**
     * @param array $data
     *
     * @return AttributeOptionInterface
     */
    protected function createAttributeOption(array $data = []): AttributeOptionInterface
    {
        $attributeOption = $this->get('pim_catalog.factory.attribute_option')->create();
        $this->get('pim_catalog.updater.attribute_option')->update($attributeOption, $data);
        $constraintList = $this->get('validator')->validate($attributeOption);
        $this->assertEquals(0, $constraintList->count());
        $this->get('pim_catalog.saver.attribute_option')->save($attributeOption);

        return $attributeOption;
    }
}
