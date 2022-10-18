<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\TableAttribute\Integration\Value;

use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Akeneo\Tool\Bundle\BatchBundle\Persistence\Sql\SqlCreateJobInstance;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Reader\Common\Creator\ReaderFactory;
use PHPUnit\Framework\Assert;

final class ExportTableValueWithLabelsIntegration extends TestCase
{
    private const CSV_EXPORT_JOB_CODE = 'csv_product_export';
    private const XLSX_EXPORT_JOB_CODE = 'xlsx_product_export';
    private JobLauncher $jobLauncher;

    /** @test */
    public function it_exports_a_table_value_with_labels_in_en_us_in_csv(): void
    {
        $config = ['header_with_label' => true, 'with_label' => true, 'withHeader' => true, 'file_locale' => 'en_US', 'with_uuid' => true];
        $csv = $this->jobLauncher->launchExport(self::CSV_EXPORT_JOB_CODE, null, $config);
        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('toto');
        $expectedContent = <<<CSV
uuid;SKU;Categories;Enabled;Family;Groups;Nutrition
{$product->getUuid()->toString()};toto;"Master catalog";Yes;;;"[{""Ingredients"":""Salt"",""Is allergenic"":""No""},{""Ingredients"":""[egg]"",""Quantity"":""2""},{""Ingredients"":""[butter]"",""Quantity"":""25"",""Is allergenic"":""Yes"",""Energy"":""3.5 kilocalorie""}]"

CSV;
        Assert::assertSame($expectedContent, $csv);
    }

    /** @test */
    public function it_exports_a_table_value_with_labels_in_fr_fr_in_csv(): void
    {
        $config = ['header_with_label' => true, 'with_label' => true, 'withHeader' => true, 'file_locale' => 'fr_FR', 'with_uuid' => true];
        $csv = $this->jobLauncher->launchExport(self::CSV_EXPORT_JOB_CODE, null, $config);
        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('toto');
        $expectedContent = <<<CSV
[uuid];[sku];Catégories;Activé;Famille;Groupes;[nutrition]
{$product->getUuid()->toString()};toto;[master];Oui;;;"[{""Ingredients"":""Sel"",""[is_allergenic]"":""Non""},{""Ingredients"":""[egg]"",""Quantité"":""2""},{""Ingredients"":""[butter]"",""Quantité"":""25"",""[is_allergenic]"":""Oui"",""[2]"":""3.5 kilocalorie""}]"

CSV;
        Assert::assertSame($expectedContent, $csv);
    }

    /** @test */
    public function it_exports_a_table_attribute_in_en_us_in_xlsx(): void
    {
        $config = ['header_with_label' => true, 'with_label' => true, 'withHeader' => true, 'file_locale' => 'en_US', 'with_uuid' => true];
        $bin = $this->jobLauncher->launchExport(self::XLSX_EXPORT_JOB_CODE, null, $config, 'xlsx');
        $tmpfile = \tempnam(\sys_get_temp_dir(), 'test_table');
        \file_put_contents($tmpfile, $bin);

        $reader = ReaderFactory::createFromType('xlsx');
        $reader->open($tmpfile);
        $sheet = current(iterator_to_array($reader->getSheetIterator()));
        /** @var Row[] $lines */
        $lines = iterator_to_array($sheet->getRowIterator());
        $reader->close();
        if (\is_file($tmpfile)) {
            \unlink($tmpfile);
        }
        $header = \array_shift($lines);

        $expectedNutritionValue = '[{"Ingredients":"Salt","Is allergenic":"No"},{"Ingredients":"[egg]","Quantity":"2"},{"Ingredients":"[butter]","Quantity":"25","Is allergenic":"Yes","Energy":"3.5 kilocalorie"}]';

        Assert::assertCount(1, $lines);
        foreach ($lines as $row) {
            $row = \array_combine($header->toArray(), $row->toArray());
            Assert::assertSame($expectedNutritionValue, $row['Nutrition']);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->jobLauncher = $this->get('akeneo_integration_tests.launcher.job_launcher');

        $this->get(SqlCreateJobInstance::class)->createJobInstance(
            [
                'code' => self::CSV_EXPORT_JOB_CODE,
                'label' => 'Test CSV',
                'job_name' => self::CSV_EXPORT_JOB_CODE,
                'status' => 0,
                'type' => 'export',
                'raw_parameters' => 'a:13:{s:7:"storage";a:2:{s:4:"type";s:5:"local";s:9:"file_path";s:38:"/tmp/export_%job_label%_%datetime%.csv";}s:9:"delimiter";s:1:";";s:9:"enclosure";s:1:""";s:10:"withHeader";b:1;s:15:"users_to_notify";a:0:{}s:21:"is_user_authenticated";b:0;s:16:"decimalSeparator";s:1:".";s:10:"dateFormat";s:10:"yyyy-MM-dd";s:10:"with_media";b:0;s:10:"with_label";b:0;s:17:"header_with_label";b:0;s:11:"file_locale";N;s:7:"filters";a:2:{s:4:"data";a:3:{i:0;a:3:{s:5:"field";s:7:"enabled";s:8:"operator";s:3:"ALL";s:5:"value";N;}i:1;a:3:{s:5:"field";s:10:"categories";s:8:"operator";s:11:"IN CHILDREN";s:5:"value";a:1:{i:0;s:6:"master";}}i:2;a:4:{s:5:"field";s:12:"completeness";s:8:"operator";s:3:"ALL";s:5:"value";i:100;s:7:"context";a:1:{s:7:"locales";a:1:{i:0;s:5:"en_US";}}}}s:9:"structure";a:2:{s:5:"scope";s:9:"ecommerce";s:7:"locales";a:1:{i:0;s:5:"en_US";}}}}',
            ]
        );
        $this->get(SqlCreateJobInstance::class)->createJobInstance(
            [
                'code' => self::XLSX_EXPORT_JOB_CODE,
                'label' => 'Test XLSX',
                'job_name' => self::XLSX_EXPORT_JOB_CODE,
                'status' => 0,
                'type' => 'export',
                'raw_parameters' => 'a:12:{s:7:"storage";a:2:{s:4:"type";s:5:"local";s:9:"file_path";s:39:"/tmp/export_%job_label%_%datetime%.xlsx";}s:10:"withHeader";b:1;s:12:"linesPerFile";i:10000;s:15:"users_to_notify";a:0:{}s:21:"is_user_authenticated";b:0;s:16:"decimalSeparator";s:1:".";s:10:"dateFormat";s:10:"yyyy-MM-dd";s:10:"with_media";b:1;s:10:"with_label";b:0;s:17:"header_with_label";b:0;s:11:"file_locale";N;s:7:"filters";a:2:{s:4:"data";a:3:{i:0;a:3:{s:5:"field";s:7:"enabled";s:8:"operator";s:3:"ALL";s:5:"value";N;}i:1;a:3:{s:5:"field";s:10:"categories";s:8:"operator";s:11:"IN CHILDREN";s:5:"value";a:1:{i:0;s:6:"master";}}i:2;a:4:{s:5:"field";s:12:"completeness";s:8:"operator";s:3:"ALL";s:5:"value";i:100;s:7:"context";a:1:{s:7:"locales";a:1:{i:0;s:5:"en_US";}}}}s:9:"structure";a:2:{s:5:"scope";s:9:"ecommerce";s:7:"locales";a:1:{i:0;s:5:"en_US";}}}}',
            ]
        );

        $this->createChannel([
            'code' => 'mobile',
            'category_tree' => 'master',
            'currencies' => ['USD'],
            'locales' => ['en_US', 'fr_FR'],
            'labels' => [],
        ]);

        $attribute = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update(
            $attribute,
            [
                'code' => 'nutrition',
                'type' => AttributeTypes::TABLE,
                'group' => 'other',
                'localizable' => false,
                'scopable' => false,
                'labels' => ['en_US' => 'Nutrition'],
                'table_configuration' => [
                    [
                        'code' => 'ingredient',
                        'data_type' => 'select',
                        'labels' => [
                            'en_US' => 'Ingredients',
                            'fr_FR' => 'Ingredients',
                        ],
                        'options' => [
                            ['code' => 'salt', 'labels' => ['en_US' => 'Salt', 'fr_FR' => 'Sel']],
                            ['code' => 'egg'],
                            ['code' => 'butter'],
                        ],
                    ],
                    [
                        'code' => 'quantity',
                        'data_type' => 'number',
                        'labels' => [
                            'en_US' => 'Quantity',
                            'fr_FR' => 'Quantité',
                        ],
                    ],
                    [
                        'code' => 'is_allergenic',
                        'data_type' => 'boolean',
                        'labels' => [
                            'en_US' => 'Is allergenic',
                        ],
                    ],
                    [
                        'code' => '2',
                        'data_type' => 'measurement',
                        'labels' => [
                            'en_US' => 'Energy',
                        ],
                        'measurement_family_code' => 'Energy',
                        'measurement_default_unit_code' => 'KILOCALORIE',
                    ],
                ],
            ]
        );
        $violations = $this->get('validator')->validate($attribute);
        Assert::assertCount(0, $violations, \sprintf('The attribute is not valid: %s', $violations));
        $this->get('pim_catalog.saver.attribute')->save($attribute);

        $this->createProduct('toto', [
            ['ingredient' => 'salt', 'is_allergenic' => false],
            ['ingredient' => 'egg', 'quantity' => 2],
            ['ingredient' => 'butter', 'quantity' => 25, 'is_allergenic' => true, '2' => ['amount' => 3.5, 'unit' => 'KILOCALORIE']],
        ]);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createChannel(array $data = []): ChannelInterface
    {
        $channel = $this->get('pim_catalog.factory.channel')->create();
        $this->get('pim_catalog.updater.channel')->update($channel, $data);

        $errors = $this->get('validator')->validate($channel);
        Assert::assertCount(0, $errors, $errors->__toString());

        $this->get('pim_catalog.saver.channel')->save($channel);

        return $channel;
    }

    private function createProduct(string $identifier, array $nutritionValue): void
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        $this->get('pim_catalog.updater.product')->update(
            $product,
            [
                'categories' => ['master'],
                'values' => [
                    'nutrition' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => $nutritionValue,
                        ],
                    ],
                ],
            ]
        );

        $violations = $this->get('pim_catalog.validator.product')->validate($product);
        Assert::assertCount(0, $violations, \sprintf('The product is not valid: %s', $violations));
        $this->get('pim_catalog.saver.product')->save($product);
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
    }
}
