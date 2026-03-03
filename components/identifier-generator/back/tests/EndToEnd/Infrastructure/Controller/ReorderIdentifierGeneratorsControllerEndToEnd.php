<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\EndToEnd\Infrastructure\Controller;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Conditions;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Delimiter;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGenerator;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorCode;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorId;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\LabelCollection;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FreeText;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Structure;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Target;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\TextTransformation;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\IdentifierGeneratorRepository;
use Akeneo\Test\Pim\Automation\IdentifierGenerator\EndToEnd\ControllerEndToEndTestCase;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ReorderIdentifierGeneratorsControllerEndToEnd extends ControllerEndToEndTestCase
{
    /** @test */
    public function it_should_redirect_on_non_xhr_request(): void
    {
        $this->loginAs('Julia');
        $this->callUpdateRoute(
            'akeneo_identifier_generator_reorder',
            [],
            ['HTTP_X-Requested-With' => 'toto'],
        );
        $response = $this->client->getResponse();
        Assert::AssertSame(Response::HTTP_FOUND, $response->getStatusCode());
        Assert::assertTrue($response->isRedirect('/'));
    }

    /** @test */
    public function it_should_return_http_forbidden_without_manage_permission(): void
    {
        $this->loginAs('mary');
        $this->callUpdateRoute('akeneo_identifier_generator_reorder', []);
        $response = $this->client->getResponse();
        Assert::AssertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    /** @test */
    public function it_reorders_identifier_generators(): void
    {
        $this->loginAs('Julia');
        $this->createIdentifierGenerator('first');
        $this->createIdentifierGenerator('second');
        $this->createIdentifierGenerator('third');

        $this->callUpdateRoute(
            'akeneo_identifier_generator_reorder',
            ['codes' => ['third', 'first', 'second']],
        );
        $response = $this->client->getResponse();
        Assert::AssertSame(Response::HTTP_OK, $response->getStatusCode());

        $orderedGenerators = $this->getGeneratorRepository()->getAll();
        Assert::assertSame(
            ['third', 'first', 'second'],
            \array_map(
                static fn (IdentifierGenerator $generator): string => $generator->code()->asString(),
                $orderedGenerators
            )
        );
    }

    private function createIdentifierGenerator(string $code): void
    {
        $this->getGeneratorRepository()->save(
            new IdentifierGenerator(
                IdentifierGeneratorId::fromString(Uuid::uuid4()->toString()),
                IdentifierGeneratorCode::fromString($code),
                Conditions::fromArray([]),
                Structure::fromArray([FreeText::fromString('abc')]),
                LabelCollection::fromNormalized([]),
                Target::fromString('sku'),
                Delimiter::fromString('-'),
                TextTransformation::fromString('no'),
            )
        );
    }

    private function getGeneratorRepository(): IdentifierGeneratorRepository
    {
        return $this->get(IdentifierGeneratorRepository::class);
    }
}
