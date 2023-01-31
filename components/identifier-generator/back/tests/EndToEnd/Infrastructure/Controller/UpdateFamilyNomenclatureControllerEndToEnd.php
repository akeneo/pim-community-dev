<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\EndToEnd\Infrastructure\Controller;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\NomenclatureDefinitionRepository;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\NomenclatureValueRepository;
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
        $this->callUpdateRoute('akeneo_identifier_generator_nomenclature_rest_update',
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
                'families' => [
                    'familyA1' => 'FAM1',
                    'familyA2' => 'FAM2',
                    'familyA3' => '',
                    'deletedFamily' => 'FOOB',
                ]
            ]),
        );
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $nomenclatureDefinition = $this->getNomenclatureDefinitionRepository()->get('family');
        Assert::assertSame($nomenclatureDefinition->operator(), '<=');
        Assert::assertSame($nomenclatureDefinition->value(), 4);
        Assert::assertSame($this->getNomenclatureValueRepository()->get('familyA1'), 'FAM1');
        Assert::assertSame($this->getNomenclatureValueRepository()->get('familyA2'), 'FAM2');
        Assert::assertSame($this->getNomenclatureValueRepository()->get('familyA3'), null);
    }

    // TODO Add failing update
    // TODO Add update when nomenclature definition already exists
    // TODO Add update with empty body => this should work

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog(['identifier_generator']);
    }

    private function getNomenclatureDefinitionRepository(): NomenclatureDefinitionRepository
    {
        return $this->get('Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\NomenclatureDefinitionRepository');
    }

    private function getNomenclatureValueRepository(): NomenclatureValueRepository
    {
        return $this->get('Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\NomenclatureValueRepository');
    }
}
