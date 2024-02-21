<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\EndToEnd\Infrastructure\Controller;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\FamilyNomenclatureRepository;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Pim\Automation\IdentifierGenerator\EndToEnd\ControllerEndToEndTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

final class UpdateFamilyNomenclatureControllerEndToEnd extends ControllerEndToEndTestCase
{
    /** @test */
    public function it_should_redirect_on_non_xhr_request(): void
    {
        $this->loginAs('Julia');
        $this->callUpdateRoute(
            'akeneo_identifier_generator_nomenclature_rest_update',
            ['propertyCode' => 'family'],
            ['HTTP_X-Requested-With' => 'toto']
        );
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_FOUND, $response->getStatusCode());
        Assert::assertTrue($response->isRedirect('/'));
    }

    /** @test */
    public function it_should_update_a_nomenclature(): void
    {
        $this->loginAs('Julia');
        $this->callUpdateRoute(
            'akeneo_identifier_generator_nomenclature_rest_update',
            ['propertyCode' => 'family'],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest'],
            \json_encode([
                'operator' => '<=',
                'value' => 4,
                'generate_if_empty' => true,
                'values' => [
                    'familyA1' => 'FAM1',
                    'familyA2' => 'FAM2',
                    'familyA3' => '',
                    'deletedFamily' => 'FOOB',
                ],
            ]),
        );
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $nomenclatureDefinition = $this->getNomenclatureRepository()->get();
        Assert::assertSame('<=', $nomenclatureDefinition->operator());
        Assert::assertSame(4, $nomenclatureDefinition->value());
        Assert::assertSame(true, $nomenclatureDefinition->generateIfEmpty());
        Assert::assertSame('FAM1', ($nomenclatureDefinition->values() ?? [])['familyA1'] ?? null);
        Assert::assertSame('FAM2', ($nomenclatureDefinition->values() ?? [])['familyA2'] ?? null);
        Assert::assertSame(null, ($nomenclatureDefinition->values() ?? [])['familyA3'] ?? null);
    }

    /** @test */
    public function it_should_not_update_a_nomenclature_with_missing_field(): void
    {
        $this->loginAs('Julia');
        $this->callUpdateRoute(
            'akeneo_identifier_generator_nomenclature_rest_update',
            ['propertyCode' => 'family'],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest'],
            \json_encode([
                'operator' => '<=',
                'generate_if_empty' => true,
            ]),
        );
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        Assert::assertSame('[{"path":"value","message":"This value should not be blank."}]', $response->getContent());
    }

    /** @test */
    public function it_should_not_work_with_invalid_json(): void
    {
        $this->loginAs('Julia');
        $this->callUpdateRoute(
            'akeneo_identifier_generator_nomenclature_rest_update',
            ['propertyCode' => 'family'],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest'],
            '[an invalid { json',
        );
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog(['identifier_generator']);
    }

    private function getNomenclatureRepository(): FamilyNomenclatureRepository
    {
        return $this->get('Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\FamilyNomenclatureRepository');
    }
}
