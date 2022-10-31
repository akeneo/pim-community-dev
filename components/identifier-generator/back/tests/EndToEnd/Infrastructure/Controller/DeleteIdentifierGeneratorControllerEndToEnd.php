<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\EndToEnd\Infrastructure\Controller;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Exception\UnableToDeleteIdentifierGeneratorException;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Conditions;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Delimiter;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGenerator;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorCode;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorId;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\LabelCollection;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FreeText;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Structure;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Target;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\IdentifierGeneratorRepository;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Pim\Automation\IdentifierGenerator\EndToEnd\ControllerEndToEndTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DeleteIdentifierGeneratorControllerEndToEnd extends ControllerEndToEndTestCase
{
    /** @test */
    public function it_should_redirect_on_non_xhr_request(): void
    {
        $this->loginAs('Julia');
        $this->callDeleteRoute(
            'akeneo_identifier_generator_rest_delete',
            [
                'code' => 'my_new_generator',
            ],
            [
                'HTTP_X-Requested-With' => 'toto',
            ]
        );
        $response = $this->client->getResponse();
        Assert::AssertSame(Response::HTTP_FOUND, $response->getStatusCode());
        Assert::assertTrue($response->isRedirect('/'));
    }

    /** @test */
    public function it_should_return_not_found_if_identifier_generator_does_not_exist(): void
    {
        $this->loginAs('Julia');
        $this->callDeleteRoute(
            'akeneo_identifier_generator_rest_delete',
            [
                'code' => 'unknown_identifier',
            ]
        );
        $response = $this->client->getResponse();
        Assert::AssertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    /** @test */
    public function it_should_throw_error_if_unable_to_delete(): void
    {
        $this->loginAs('Julia');

        $identifierRepositoryMock = $this->createMock(IdentifierGeneratorRepository::class);
        $identifierRepositoryMock
            ->method('get')
            ->willReturn(new IdentifierGenerator(
                IdentifierGeneratorId::fromString('2038e1c9-68ff-4833-b06f-01e42d206002'),
                IdentifierGeneratorCode::fromString('aabbcc'),
                Conditions::fromArray([]),
                Structure::fromArray([FreeText::fromString('abc')]),
                LabelCollection::fromNormalized(['fr' => 'Générateur']),
                Target::fromString('sku'),
                Delimiter::fromString('-'),
            ));

        $identifierRepositoryMock
            ->method('delete')
            ->willThrowException(new UnableToDeleteIdentifierGeneratorException('exception'));

        self::getContainer()->set(IdentifierGeneratorRepository::class, $identifierRepositoryMock);

        $this->callDeleteRoute(
            'akeneo_identifier_generator_rest_delete',
            [
                'code' => 'my_new_generator',
            ]
        );
        $response = $this->client->getResponse();
        Assert::AssertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        Assert::assertEquals('"exception"', $response->getContent());
    }

    /** @test */
    public function it_should_return_accepted_if_identifier_generator_is_deleted(): void
    {
        $identifierRepository = $this->get(IdentifierGeneratorRepository::class);

        $this->loginAs('Julia');
        $this->callCreateRoute(
            'akeneo_identifier_generator_rest_create',
            ['HTTP_X-Requested-With' => 'XMLHttpRequest'],
            \json_encode([
                'code' => 'my_new_generator',
                'labels' => [
                    'en_US' => 'My new generator',
                    'fr_FR' => 'Mon nouveau générateur',
                ],
                'target' => 'sku',
                'conditions' => [],
                'structure' => [[
                    'type' => 'free_text',
                    'string' => 'AKN',
                ]],
                'delimiter' => null,
            ]),
        );

        Assert::assertSame(1, $identifierRepository->count());

        $this->callDeleteRoute(
            'akeneo_identifier_generator_rest_delete',
            [
                'code' => 'my_new_generator',
            ]
        );
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_ACCEPTED, $response->getStatusCode());
        Assert::assertSame(0, $identifierRepository->count());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
