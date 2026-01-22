<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\EndToEnd\Infrastructure\Controller;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Update\UpdateNomenclatureCommand;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Update\UpdateNomenclatureHandler;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Pim\Automation\IdentifierGenerator\EndToEnd\ControllerEndToEndTestCase;
use Akeneo\Test\Pim\Enrichment\Product\Helper\FeatureHelper;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

final class GetNomenclatureControllerEndToEnd extends ControllerEndToEndTestCase
{
    /** @test */
    public function it_should_redirect_on_non_xhr_request(): void
    {
        $this->loginAs('Julia');
        $this->callRoute(
            'akeneo_identifier_generator_nomenclature_rest_get',
            ['HTTP_X-Requested-With' => 'toto'],
            ['propertyCode' => 'family'],
        );
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_FOUND, $response->getStatusCode());
        Assert::assertTrue($response->isRedirect('/'));
    }

    /** @test */
    public function it_should_get_an_existing_family_nomenclature(): void
    {
        $this->loginAs('Julia');
        $this->createNomenclature('family', [
            'familyA1' => 'FA1',
            'familyA2' => 'FA2',
        ]);

        $this->callRoute(
            'akeneo_identifier_generator_nomenclature_rest_get',
            ['HTTP_X-Requested-With' => 'XMLHttpRequest'],
            ['propertyCode' => 'family'],
        );
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode());
        Assert::assertEqualsCanonicalizing([
            'operator' => '=',
            'value'=> 3,
            'generate_if_empty'=> false,
            'values'=> ['FamilyA1' => 'FA1', 'FamilyA2' => 'FA2'],
        ], \json_decode($response->getContent(), true));
    }

    /** @test */
    public function it_should_get_a_non_existing_family_nomenclature(): void
    {
        $this->loginAs('Julia');
        $this->callRoute(
            'akeneo_identifier_generator_nomenclature_rest_get',
            ['HTTP_X-Requested-With' => 'XMLHttpRequest'],
            ['propertyCode' => 'family'],
        );
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode());
        Assert::assertJsonStringEqualsJsonString(
            <<<JSON
            {
              "operator": null,
              "value": null,
              "generate_if_empty": null,
              "values": {}
            }
            JSON,
            $response->getContent()
        );
    }

    /** @test */
    public function it_should_get_an_existing_ref_entity_nomenclature(): void
    {
        $values = ['akeneo' => 'akn', 'adidas' => 'adds'];
        FeatureHelper::skipIntegrationTestWhenReferenceEntityIsNotActivated();
        $this->loginAs('Julia');
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

        $this->createNomenclature('a_reference_entity_attribute', $values);
        $this->callRoute(
            'akeneo_identifier_generator_nomenclature_rest_get',
            ['HTTP_X-Requested-With' => 'XMLHttpRequest'],
            ['propertyCode' => 'a_reference_entity_attribute'],
        );
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode());
        Assert::assertEqualsCanonicalizing([
            'operator' => '=',
            'value'=> 3,
            'generate_if_empty'=> false,
            'values'=> $values,
        ], \json_decode($response->getContent(), true));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog(['identifier_generator']);
    }

    private function getUpdateNomenclatureHandler(): UpdateNomenclatureHandler
    {
        return $this->get('Akeneo\Pim\Automation\IdentifierGenerator\Application\Update\UpdateNomenclatureHandler');
    }

    private function createNomenclature(string $propertyCode, array $values): void
    {
        $command = new UpdateNomenclatureCommand($propertyCode, '=', 3, false, $values);

        ($this->getUpdateNomenclatureHandler())($command);
    }
}
