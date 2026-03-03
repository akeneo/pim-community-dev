<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Integration\Import;

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
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

class ImportProductsIntegration extends TestCase
{
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    /** @test */
    public function it_does_not_save_warning_if_no_identifier_generator(): void
    {
        Assert::assertEquals(0, $this->getProductsCount());
        $this->importEmptyProduct();
        Assert::assertEquals([], $this->getImportWarnings());
        Assert::assertEquals(1, $this->getProductsCount());
    }

    /** @test */
    public function it_does_not_save_warning_if_no_identifiers_are_valid(): void
    {
        Assert::assertEquals(0, $this->getProductsCount());
        $this->createValidIdentifierGenerator();
        $this->importEmptyProduct();
        Assert::assertEquals([], $this->getImportWarnings());
        Assert::assertEquals(1, $this->getProductsCount());
    }

    /** @test */
    public function it_saves_warnings_if_no_identifiers_are_invalid(): void
    {
        Assert::assertEquals(0, $this->getProductsCount());
        $this->createStupidIdentifierGenerator();
        $this->importEmptyProduct();
        Assert::assertEquals([
            [
                'reason' => "Your product has been saved but your identifier could not be generated:
sku: The sku attribute must not contain more than 255 characters. The submitted value is too long.",
                'item' => [
                    'sku' => "aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa-aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa-aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa-aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa-aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa-aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa",
                ],
            ],
        ], $this->getImportWarnings());
        Assert::assertEquals(1, $this->getProductsCount());
    }

    private function getJobLauncher(): JobLauncher
    {
        return $this->get('akeneo_integration_tests.launcher.job_launcher');
    }

    private function createValidIdentifierGenerator(): void
    {
        $identifierGenerator = new IdentifierGenerator(
            IdentifierGeneratorId::fromString('d556e59e-d46c-465e-863d-f4a39d0b7485'),
            IdentifierGeneratorCode::fromString('abcdef'),
            Conditions::fromArray([]),
            Structure::fromArray([FreeText::fromString('abc')]),
            LabelCollection::fromNormalized(['fr' => 'Générateur']),
            Target::fromString('sku'),
            Delimiter::fromString('-'),
            TextTransformation::fromString('no'),
        );

        $this->getIdentifierGeneratorRepository()->save($identifierGenerator);
    }

    private function createStupidIdentifierGenerator(): void
    {
        $identifierGenerator = new IdentifierGenerator(
            IdentifierGeneratorId::fromString('d556e59e-d46c-465e-863d-f4a39d0b7485'),
            IdentifierGeneratorCode::fromString('abcdef'),
            Conditions::fromArray([]),
            Structure::fromArray([
                FreeText::fromString(\str_repeat('a', 50)),
                FreeText::fromString(\str_repeat('a', 50)),
                FreeText::fromString(\str_repeat('a', 50)),
                FreeText::fromString(\str_repeat('a', 50)),
                FreeText::fromString(\str_repeat('a', 50)),
                FreeText::fromString(\str_repeat('a', 50)),
            ]),
            LabelCollection::fromNormalized(['fr' => 'Générateur']),
            Target::fromString('sku'),
            Delimiter::fromString('-'),
            TextTransformation::fromString('no'),
        );

        $this->getIdentifierGeneratorRepository()->save($identifierGenerator);
    }

    private function getIdentifierGeneratorRepository(): IdentifierGeneratorRepository
    {
        return $this->get(IdentifierGeneratorRepository::class);
    }

    private function importEmptyProduct(): void
    {
        $content = <<<CSV
        uuid;sku
        ;
        CSV;

        $this->getJobLauncher()->launchImport('csv_product_import', $content);
    }

    private function getImportWarnings(): array
    {
        $results = $this->getConnection()->fetchAllAssociative('SELECT reason, item FROM akeneo_batch_warning;');

        return \array_map(
            fn (array $line): array => ['reason' => $line['reason'], 'item' => \unserialize($line['item'])],
            $results
        );
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }

    private function getProductsCount(): int
    {
        return (int) $this->getConnection()->fetchOne('SELECT COUNT(1) AS c FROM pim_catalog_product');
    }
}
