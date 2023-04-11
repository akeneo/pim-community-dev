<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Integration\Repository;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Exception\CouldNotFindIdentifierGeneratorException;
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
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\TextTransformation;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\IdentifierGeneratorRepository;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;

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
    public function it_saves_identifier_generators(): void
    {
        $this->identifierGeneratorRepository->save(
            $this->getIdentifierGenerator(code: 'identifier_generator0')
        );
        $this->assertEquals(0, $this->getSortOrder('identifier_generator0'));

        $this->identifierGeneratorRepository->save(
            $this->getIdentifierGenerator(code: 'identifier_generator1')
        );
        $this->assertEquals(1, $this->getSortOrder('identifier_generator1'));
    }

    /** @test */
    public function it_updates_an_identifier_generator(): void
    {
        $identifierGenerator =$this->getIdentifierGenerator(code: 'identifier_generator0');
        $this->identifierGeneratorRepository->save($identifierGenerator);

        $this->identifierGeneratorRepository->update(
            $identifierGenerator->withLabelCollection(LabelCollection::fromNormalized(['fr' => 'Générateur mis à jour']))
        );

        Assert::assertEquals(1, $this->identifierGeneratorRepository->count());

        $identifierGeneratorUpdated = $this->identifierGeneratorRepository->get('identifier_generator0');
        Assert::assertInstanceOf(IdentifierGenerator::class, $identifierGeneratorUpdated);
        Assert::assertEquals($identifierGenerator->id()->asString(), $identifierGeneratorUpdated->id()->asString());
        Assert::assertEquals($identifierGenerator->code()->asString(), $identifierGeneratorUpdated->code()->asString());
        Assert::assertEquals($identifierGenerator->target()->asString(), $identifierGeneratorUpdated->target()->asString());
        Assert::assertEquals($identifierGenerator->delimiter()->asString(), $identifierGeneratorUpdated->delimiter()->asString());
        Assert::assertEquals(['fr' => 'Générateur mis à jour'], $identifierGeneratorUpdated->labelCollection()->normalize());
        Assert::assertEquals($identifierGenerator->conditions()->normalize(), $identifierGeneratorUpdated->conditions()->normalize());
        Assert::assertEquals($identifierGenerator->structure()->normalize(), $identifierGeneratorUpdated->structure()->normalize());
    }

    /** @test */
    public function it_gets_an_identifier_generator(): void
    {
        $identifierGenerator = $this->getIdentifierGenerator('default');
        $this->identifierGeneratorRepository->save($identifierGenerator);

        $identifierGeneratorFromDB = $this->identifierGeneratorRepository->get('default');

        Assert::assertInstanceOf(IdentifierGenerator::class, $identifierGenerator);
        Assert::assertEquals($identifierGenerator->id()->asString(), $identifierGeneratorFromDB->id()->asString());
        Assert::assertEquals($identifierGenerator->code()->asString(), $identifierGeneratorFromDB->code()->asString());
        Assert::assertEquals($identifierGenerator->target()->asString(), $identifierGeneratorFromDB->target()->asString());
        Assert::assertEquals($identifierGenerator->delimiter()->asString(), $identifierGeneratorFromDB->delimiter()->asString());
        Assert::assertEquals($identifierGenerator->labelCollection()->normalize(), $identifierGeneratorFromDB->labelCollection()->normalize());
        Assert::assertEquals($identifierGenerator->conditions()->normalize(), $identifierGeneratorFromDB->conditions()->normalize());
        Assert::assertEquals($identifierGenerator->structure()->normalize(), $identifierGeneratorFromDB->structure()->normalize());
    }

    /** @test */
    public function it_gets_an_identifier_generator_case_insensitive(): void
    {
        $identifierGenerator = $this->getIdentifierGenerator('default');
        $this->identifierGeneratorRepository->save($identifierGenerator);

        $identifierGeneratorFromDB = $this->identifierGeneratorRepository->get('dEfAuLt');

        Assert::assertInstanceOf(IdentifierGenerator::class, $identifierGenerator);
        Assert::assertEquals($identifierGenerator->id()->asString(), $identifierGeneratorFromDB->id()->asString());
        Assert::assertEquals($identifierGenerator->code()->asString(), $identifierGeneratorFromDB->code()->asString());
        Assert::assertEquals($identifierGenerator->target()->asString(), $identifierGeneratorFromDB->target()->asString());
        Assert::assertEquals($identifierGenerator->delimiter()->asString(), $identifierGeneratorFromDB->delimiter()->asString());
        Assert::assertEquals($identifierGenerator->labelCollection()->normalize(), $identifierGeneratorFromDB->labelCollection()->normalize());
        Assert::assertEquals($identifierGenerator->conditions()->normalize(), $identifierGeneratorFromDB->conditions()->normalize());
        Assert::assertEquals($identifierGenerator->structure()->normalize(), $identifierGeneratorFromDB->structure()->normalize());
    }

    /** @test */
    public function it_throws_an_exception_if_identifier_code_already_exists(): void
    {
        $this->identifierGeneratorRepository->save($this->getIdentifierGenerator(code: 'same_code'));
        $this->expectException(UnableToSaveIdentifierGeneratorException::class);
        $this->identifierGeneratorRepository->save($this->getIdentifierGenerator(code: 'same_code'));
    }

    /** @test */
    public function it_throws_an_exception_if_identifier_code_already_exists_with_different_case(): void
    {
        $this->identifierGeneratorRepository->save($this->getIdentifierGenerator(code: 'same_code'));
        $this->expectException(UnableToSaveIdentifierGeneratorException::class);
        $this->identifierGeneratorRepository->save($this->getIdentifierGenerator(code: 'sAmE_cOde'));
    }

    /** @test */
    public function its_gets_an_unknown_identifier_generator(): void
    {
        $this->expectException(CouldNotFindIdentifierGeneratorException::class);
        $this->identifierGeneratorRepository->get('unknown');
    }

    /** @test */
    public function its_gets_all_identifier_generator(): void
    {
        $firstIdentifierGenerator = $this->getIdentifierGenerator(code: 'identifier_generator0');
        $this->identifierGeneratorRepository->save($firstIdentifierGenerator);
        $this->identifierGeneratorRepository->save($this->getIdentifierGenerator(code: 'identifier_generator1'));

        $identifiersGeneratorsFromDB = $this->identifierGeneratorRepository->getAll();
        Assert::assertContainsOnlyInstancesOf(IdentifierGenerator::class, $identifiersGeneratorsFromDB);
        Assert::assertCount(2, $identifiersGeneratorsFromDB);

        $firstIdentifierFromDB = $identifiersGeneratorsFromDB[0];
        Assert::assertInstanceOf(IdentifierGenerator::class, $firstIdentifierFromDB);
        Assert::assertEquals($firstIdentifierGenerator->id()->asString(), $firstIdentifierFromDB->id()->asString());
        Assert::assertEquals('identifier_generator0', $firstIdentifierFromDB->code()->asString());
        Assert::assertEquals($firstIdentifierGenerator->target()->asString(), $firstIdentifierFromDB->target()->asString());
        Assert::assertEquals($firstIdentifierGenerator->delimiter()->asString(), $firstIdentifierFromDB->delimiter()->asString());
        Assert::assertEquals($firstIdentifierGenerator->labelCollection()->normalize(), $firstIdentifierFromDB->labelCollection()->normalize());
        Assert::assertEquals($firstIdentifierGenerator->conditions()->normalize(), $firstIdentifierFromDB->conditions()->normalize());
        Assert::assertEquals($firstIdentifierGenerator->structure()->normalize(), $firstIdentifierFromDB->structure()->normalize());

        $secondIdentifierFromDB = $identifiersGeneratorsFromDB[1];
        Assert::assertEquals($secondIdentifierFromDB->code()->asString(), 'identifier_generator1');
    }

    /** @test */
    public function it_can_delete_an_identifier_generator_and_do_reorder(): void
    {
        $this->identifierGeneratorRepository->save($this->getIdentifierGenerator(code: 'identifier_generator0'));
        $this->identifierGeneratorRepository->save($this->getIdentifierGenerator(code: 'identifier_generator1'));
        $this->identifierGeneratorRepository->save($this->getIdentifierGenerator(code: 'identifier_generator2'));
        $this->identifierGeneratorRepository->save($this->getIdentifierGenerator(code: 'identifier_generator3'));

        Assert::assertEquals(4, $this->identifierGeneratorRepository->count());

        $this->identifierGeneratorRepository->delete('identifier_generator1');
        Assert::assertEquals(3, $this->identifierGeneratorRepository->count());

        Assert::assertEquals(0, $this->getSortOrder('identifier_generator0'));
        Assert::assertEquals(1, $this->getSortOrder('identifier_generator2'));
        Assert::assertEquals(2, $this->getSortOrder('identifier_generator3'));
    }

    /** @test */
    public function it_can_delete_an_identifier_generator_regardless_of_case(): void
    {
        $this->identifierGeneratorRepository->save($this->getIdentifierGenerator(code: 'identifier_generator0'));

        Assert::assertEquals(1, $this->identifierGeneratorRepository->count());

        $this->identifierGeneratorRepository->delete('identifier_generator0');
        Assert::assertEquals(0, $this->identifierGeneratorRepository->count());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog(['identifier_generator']);
    }

    private function getSortOrder(string $code): int
    {
        $sql = <<<SQL
SELECT sort_order 
FROM pim_catalog_identifier_generator
WHERE code=:code
SQL;

        return \intval($this->connection->fetchOne($sql, ['code' => $code]));
    }

    private function getIdentifierGenerator(?string $code = null): IdentifierGenerator
    {
        return new IdentifierGenerator(
            IdentifierGeneratorId::fromString(Uuid::uuid4()->toString()),
            IdentifierGeneratorCode::fromString($code ?? 'default'),
            Conditions::fromArray([]),
            Structure::fromArray([FreeText::fromString('abc')]),
            LabelCollection::fromNormalized(['fr' => 'Générateur']),
            Target::fromString('sku'),
            Delimiter::fromString('-'),
            TextTransformation::fromString('no'),
        );
    }
}
