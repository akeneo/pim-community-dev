<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Integration\Repository;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Exception\UnableToSaveIdentifierGeneratorException;
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
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlIdentifierGeneratorRepositoryIntegration extends TestCase
{
    private IdentifierGeneratorRepository $identifierGeneratorRepository;
    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->identifierGeneratorRepository = $this->get(IdentifierGeneratorRepository::class);
        $this->connection = $this->get('database_connection');
    }

    /** @test */
    public function it_saves_an_identifier_generator(): void
    {
        $identifierGenerator = new IdentifierGenerator(
            IdentifierGeneratorId::fromString('d556e59e-d46c-465e-863d-f4a39d0b7485'),
            IdentifierGeneratorCode::fromString('abcdef'),
            Conditions::fromArray([]),
            Structure::fromArray([FreeText::fromString('abc')]),
            LabelCollection::fromNormalized(['fr' => 'Générateur']),
            Target::fromString('sku'),
            Delimiter::fromString('-'),
        );

        $this->identifierGeneratorRepository->save($identifierGenerator);
    }

    /** @test */
    public function it_gets_an_identifier_generator(): void
    {
        $query = <<<SQL
INSERT INTO pim_catalog_identifier_generator (uuid, code, target, delimiter, labels, conditions, structure)
VALUES (UUID_TO_BIN('2038e1c9-68ff-4833-b06f-01e42d206002'), 'default', 'sku_default', '-', '{"fr": "Structure par defaut"}', '{}', '[{"type": "free_text", "string": "default_structure"}]');
SQL;

        $this->connection->executeStatement($query);

        $identifierGenerator = $this->identifierGeneratorRepository->get('default');

        Assert::assertInstanceOf(IdentifierGenerator::class, $identifierGenerator);
        Assert::assertEquals($identifierGenerator->id()->asString(), '2038e1c9-68ff-4833-b06f-01e42d206002');
        Assert::assertEquals($identifierGenerator->code()->asString(), 'default');
        Assert::assertEquals($identifierGenerator->target()->asString(), 'sku_default');
        Assert::assertEquals($identifierGenerator->delimiter()->asString(), '-');
        Assert::assertEquals($identifierGenerator->labelCollection()->normalize(), ['fr' => 'Structure par defaut']);
        Assert::assertEquals($identifierGenerator->conditions()->normalize(), []);
        Assert::assertEquals($identifierGenerator->structure()->normalize(), [[
            'type' => 'free_text',
            'string' => 'default_structure',
        ],]);
    }

    /** @test */
    public function it_throws_an_exception_if_identifier_code_already_exists(): void
    {
        $identifierGenerator = new IdentifierGenerator(
            IdentifierGeneratorId::fromString('d556e59e-d46c-465e-863d-f4a39d0b7485'),
            IdentifierGeneratorCode::fromString('abcdef'),
            Conditions::fromArray([]),
            Structure::fromArray([FreeText::fromString('abc')]),
            LabelCollection::fromNormalized(['fr' => 'Générateur']),
            Target::fromString('sku'),
            Delimiter::fromString('-'),
        );
        $this->identifierGeneratorRepository->save($identifierGenerator);

        $identifierGenerator2 = new IdentifierGenerator(
            IdentifierGeneratorId::fromString('d556e59e-d46c-465e-863d-f4a39d0b7485'),
            IdentifierGeneratorCode::fromString('abcdef'),
            Conditions::fromArray([]),
            Structure::fromArray([FreeText::fromString('abc')]),
            LabelCollection::fromNormalized(['fr' => 'Générateur']),
            Target::fromString('sku'),
            Delimiter::fromString('-'),
        );

        $this->expectException(UnableToSaveIdentifierGeneratorException::class);

        $this->identifierGeneratorRepository->save($identifierGenerator2);
    }

    /** @test */
    public function its_gets_an_unknown_identifier_generator(): void
    {
        $identifierGenerator = $this->identifierGeneratorRepository->get('unknown');

        Assert::assertEquals($identifierGenerator, null);
    }

    /** @test */
    /** @test */
    public function its_gets_all_identifier_generator(): void
    {
        $query = <<<SQL
INSERT INTO pim_catalog_identifier_generator (uuid, code, target, delimiter, labels, conditions, structure)
VALUES (UUID_TO_BIN('2038e1c9-68ff-4833-b06f-01e42d206002'), 'default', 'sku_default', '-', '{"fr": "Structure par defaut"}', '{}', '[{"type": "free_text", "string": "default_structure"}]');
SQL;

        $this->connection->executeStatement($query);

        $identifiersGenerators = $this->identifierGeneratorRepository->getAll();
        Assert::assertContainsOnlyInstancesOf(IdentifierGenerator::class, $identifiersGenerators);

        $firstIdentifier = $identifiersGenerators[0];
        Assert::assertInstanceOf(IdentifierGenerator::class, $firstIdentifier);
        Assert::assertEquals($firstIdentifier->id()->asString(), '2038e1c9-68ff-4833-b06f-01e42d206002');
        Assert::assertEquals($firstIdentifier->code()->asString(), 'default');
        Assert::assertEquals($firstIdentifier->target()->asString(), 'sku_default');
        Assert::assertEquals($firstIdentifier->delimiter()->asString(), '-');
        Assert::assertEquals($firstIdentifier->labelCollection()->normalize(), ['fr' => 'Structure par defaut']);
        Assert::assertEquals($firstIdentifier->conditions()->normalize(), []);
        Assert::assertEquals($firstIdentifier->structure()->normalize(), [[
            'type' => 'free_text',
            'string' => 'default_structure',
        ],]);
    }

    /** @test */
    public function it_can_delete_an_identifier_generator(): void
    {
        $query = <<<SQL
INSERT INTO pim_catalog_identifier_generator (uuid, code, target, delimiter, labels, conditions, structure)
VALUES (UUID_TO_BIN('2038e1c9-68ff-4833-b06f-01e42d206002'), 'default', 'sku_default', '-', '{"fr": "Structure par defaut"}', '{}', '[{"type": "free_text", "string": "default_structure"}]');
SQL;

        $this->connection->executeStatement($query);
        Assert::assertEquals($this->identifierGeneratorRepository->count(), 1);

        $this->identifierGeneratorRepository->delete('default');
        Assert::assertEquals($this->identifierGeneratorRepository->count(), 0);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
