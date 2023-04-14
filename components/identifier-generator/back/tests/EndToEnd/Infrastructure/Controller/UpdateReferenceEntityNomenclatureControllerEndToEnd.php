<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\EndToEnd\Infrastructure\Controller;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\ReferenceEntityNomenclatureRepository;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Pim\Automation\IdentifierGenerator\EndToEnd\ControllerEndToEndTestCase;
use Akeneo\Test\Pim\Enrichment\Product\Helper\FeatureHelper;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

final class UpdateReferenceEntityNomenclatureControllerEndToEnd extends ControllerEndToEndTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        FeatureHelper::skipIntegrationTestWhenReferenceEntityIsNotActivated();

        $this->createReferenceEntity('brand', ['en_US' => 'Brands']);
        $this->createRecords('brand', ['akeneo', 'adidas', 'zara']);
        $this->createAttribute(
            [
                'code' => 'a_reference_entity_attribute',
                'type' => 'akeneo_reference_entity',
                'group' => 'other',
                'reference_data_name' => 'brand',
            ]
        );
    }

    /** @test */
    public function it_should_redirect_on_non_xhr_request(): void
    {
        $this->loginAs('Julia');
        $this->callUpdateRoute(
            'akeneo_identifier_generator_nomenclature_rest_update',
            ['propertyCode' => 'a_reference_entity_attribute'],
            ['HTTP_X-Requested-With' => 'toto']
        );
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_FOUND, $response->getStatusCode());
        Assert::assertTrue($response->isRedirect('/'));
    }

    /** @test */
    public function it_should_create_and_update_a_nomenclature(): void
    {
        $this->loginAs('Julia');

        $this->callUpdateRoute(
            'akeneo_identifier_generator_nomenclature_rest_update',
            ['propertyCode' => 'a_reference_entity_attribute'],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest'],
            \json_encode([
                'operator' => '<=',
                'value' => 4,
                'generate_if_empty' => true,
                'values' => [
                    'akeneo' => 'akn',
                    'adidas' => 'adds',
                    'zara' => '',
                    'unknown' => 'TOTO',
                ],
            ]),
        );
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $nomenclatureDefinition = $this->getNomenclatureRepository()->get('a_reference_entity_attribute');
        Assert::assertSame($nomenclatureDefinition->operator(), '<=');
        Assert::assertSame($nomenclatureDefinition->value(), 4);
        Assert::assertSame($nomenclatureDefinition->generateIfEmpty(), true);
        Assert::assertSame(($nomenclatureDefinition->values() ?? [])['akeneo'] ?? null, 'akn');
        Assert::assertSame(($nomenclatureDefinition->values() ?? [])['adidas'] ?? null, 'adds');
        Assert::assertSame(($nomenclatureDefinition->values() ?? [])['zara'] ?? null, null);
        Assert::assertSame(($nomenclatureDefinition->values() ?? [])['unknown'] ?? null, null);

        $this->callUpdateRoute(
            'akeneo_identifier_generator_nomenclature_rest_update',
            ['propertyCode' => 'a_reference_entity_attribute'],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest'],
            \json_encode([
                'operator' => '<=',
                'value' => 4,
                'generate_if_empty' => true,
                'values' => [
                    'adidas' => null,
                    'zara' => 'zr',
                ],
            ]),
        );
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $nomenclatureDefinition = $this->getNomenclatureRepository()->get('a_reference_entity_attribute');
        Assert::assertSame(($nomenclatureDefinition->values() ?? [])['akeneo'] ?? null, 'akn');
        Assert::assertSame(($nomenclatureDefinition->values() ?? [])['adidas'] ?? null, null);
        Assert::assertSame(($nomenclatureDefinition->values() ?? [])['zara'] ?? null, 'zr');
    }

    /** @test */
    public function it_should_throw_404_when_ref_entity_attribute_does_not_exists(): void
    {
        $this->loginAs('Julia');
        $this->callUpdateRoute(
            'akeneo_identifier_generator_nomenclature_rest_update',
            ['propertyCode' => 'unknown_ref_entity_attribute'],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest'],
            \json_encode([
                'operator' => '<=',
                'value' => 4,
                'generate_if_empty' => true,
                'values' => [],
            ]),
        );
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog(['identifier_generator']);
    }

    private function getNomenclatureRepository(): ReferenceEntityNomenclatureRepository
    {
        return $this->get(ReferenceEntityNomenclatureRepository::class);
    }
}
