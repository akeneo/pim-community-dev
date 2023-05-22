<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Integration\Repository;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\NomenclatureDefinition;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\ReferenceEntityNomenclatureRepository;
use Akeneo\Test\Pim\Automation\IdentifierGenerator\EndToEnd\ControllerEndToEndTestCase;
use Akeneo\Test\Pim\Enrichment\Product\Helper\FeatureHelper;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlReferenceEntityNomenclatureRepositoryIntegration extends ControllerEndToEndTestCase
{
    private ReferenceEntityNomenclatureRepository $referenceEntityNomenclatureRepository;

    protected function setUp(): void
    {
        parent::setUp();
        FeatureHelper::skipIntegrationTestWhenReferenceEntityIsNotActivated();
        $this->referenceEntityNomenclatureRepository = $this->get(ReferenceEntityNomenclatureRepository::class);

        $this->createReferenceEntity('brand', ['en_US' => 'Brand']);
        $this->createRecords('brand', ['akeneo', 'adidas', 'nike']);

        $this->createAttribute([
            'code' => 'a_reference_entity_attribute',
            'type' => 'akeneo_reference_entity',
            'group' => 'other',
            'reference_data_name' => 'brand',
        ]);
    }

    /** @test */
    public function it_saves_ref_entity_nomenclature(): void
    {
        FeatureHelper::skipIntegrationTestWhenReferenceEntityIsNotActivated();

        $refEntityNomenclatureDefinition = new NomenclatureDefinition(
            '=',
            5,
            false,
            ['akeneo' => 'akn', 'adidas' => 'add']
        );

        $this->referenceEntityNomenclatureRepository->update(
            'a_reference_entity_attribute',
            $refEntityNomenclatureDefinition
        );

        $this->assertSameNomenclatureDefinition($refEntityNomenclatureDefinition, $this->referenceEntityNomenclatureRepository->get('a_reference_entity_attribute'));
    }

    /** @test */
    public function it_updates_a_ref_entity_nomenclature(): void
    {
        FeatureHelper::skipIntegrationTestWhenReferenceEntityIsNotActivated();
        $refEntityNomenclatureDefinition = new NomenclatureDefinition(
            '=',
            5,
            false,
            ['akeneo' => 'akn', 'adidas' => 'add']
        );
        $this->referenceEntityNomenclatureRepository->update(
            'a_reference_entity_attribute',
            $refEntityNomenclatureDefinition
        );

        $updateRefEntityNomenclatureDefinition = new NomenclatureDefinition(
            '<=',
            5,
            false,
            ['akeneo' => null, 'nike' => 'nii']
        );
        $this->referenceEntityNomenclatureRepository->update(
            'a_reference_entity_attribute',
            $updateRefEntityNomenclatureDefinition
        );

        $expected = new NomenclatureDefinition(
            '<=',
            5,
            false,
            ['adidas' => 'add', 'nike' => 'nii']
        );

        $this->assertSameNomenclatureDefinition($expected, $this->referenceEntityNomenclatureRepository->get('a_reference_entity_attribute'));
    }

    /** @test */
    public function it_saves_and_updates_a_simple_select_nomenclature_with_different_case(): void
    {
        FeatureHelper::skipIntegrationTestWhenReferenceEntityIsNotActivated();
        $this->referenceEntityNomenclatureRepository->update(
            'a_referENCE_enTITY_attribute',
            new NomenclatureDefinition(
                '=',
                5,
                false,
                ['akeneo' => 'akn', 'adidas' => 'add']
            )
        );

        $this->assertSameNomenclatureDefinition(
            new NomenclatureDefinition(
                '=',
                5,
                false,
                ['akeneo' => 'akn', 'adidas' => 'add']
            ),
            $this->referenceEntityNomenclatureRepository->get('a_reference_entity_attribute')
        );
    }

    private function assertSameNomenclatureDefinition(NomenclatureDefinition $expected, NomenclatureDefinition $result): void
    {
        Assert::assertEquals($expected->operator(), $result->operator());
        Assert::assertEquals($expected->value(), $result->value());
        Assert::assertEquals($expected->generateIfEmpty(), $result->generateIfEmpty());
        Assert::assertEqualsCanonicalizing($expected->values(), $result->values());
    }
}
