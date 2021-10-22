<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\EndToEnd\ImportExport\InternalApi;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\BatchBundle\Persistence\Sql\SqlCreateJobInstance;
use AkeneoTest\Platform\EndToEnd\InternalApiTestCase;
use PHPUnit\Framework\Assert;

/**
 * Tests the zip archive generation when an export job generates more than 1 file
 */
class GenerateZipArchiveEndToEnd extends InternalApiTestCase
{
    private ?string $tmpFile = null;

    /** @test */
    public function it_generates_a_zip_archive_when_an_export_job_generated_at_least_two_files()
    {
        $csv = $this->get('akeneo_integration_tests.launcher.job_launcher')->launchExport(
            'csv_product_export_with_media',
            'admin',
            []
        );
        Assert::assertSame(<<<CSV
            sku;categories;enabled;family;groups;an_image;a_file
            sku1;categoryA;1;familyA;;files/sku1/an_image/akeneo.png;files/sku1/a_file/akeneo.pdf
            sku2;categoryA;1;familyA;;files/sku2/an_image/akeneo.jpg;
            
            CSV,
            $csv
        );

        $jobExecutionId = $this->get('database_connection')->executeQuery(
            <<<SQL
            SELECT ex.id
            FROM akeneo_batch_job_execution ex
            INNER JOIN akeneo_batch_job_instance inst ON ex.job_instance_id = inst.id
            WHERE inst.code = 'csv_product_export_with_media'
            SQL
        )->fetchColumn();
        $this->authenticate($this->getAdminUser());

        // get the content of the generated zip file
        $this->client->followMetaRefresh(true);
        \ob_start();
        $this->client->request('GET', \sprintf('job/%s/download/zip', $jobExecutionId));
        $binaryContent = \ob_get_contents();
        \ob_end_clean();

        Assert::assertTrue($this->client->getResponse()->isOk());
        Assert::assertNotEmpty($binaryContent);

        \file_put_contents($this->tmpFile, $binaryContent);
        $zip = new \ZipArchive();
        $zip->open($this->tmpFile);
        $actualFiles = [];
        for ($index = 0; $index < $zip->count(); $index++) {
            $actualFiles[] = $zip->statIndex($index)['name'] ?? null;
        }
        $zip->close();
        Assert::assertEqualsCanonicalizing(
            [
                'export.csv',
                'files/sku1/an_image/akeneo.png',
                'files/sku1/a_file/akeneo.pdf',
                'files/sku2/an_image/akeneo.jpg',
            ],
            $actualFiles
        );
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->tmpFile = \sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'pim-e2e-test-zip/export_with_media.test.zip';
        if (!\file_exists(\dirname($this->tmpFile))) {
            \mkdir(\dirname($this->tmpFile), 0777, true);
        }

        $this->createProduct('sku1', [
            'family' => 'familyA',
            'categories' => ['categoryA'],
            'values' => [
                'an_image' => [
                    [
                        'scope' => null,
                        'locale' => null,
                        'data' => $this->getFileInfoKey($this->getFixturePath('akeneo.png')),
                    ],
                ],
                'a_file' => [
                    [
                        'scope' => null,
                        'locale' => null,
                        'data' => $this->getFileInfoKey($this->getFixturePath('akeneo.pdf')),
                    ],
                ],
            ],
        ]);
        $this->createProduct('sku2', [
            'family' => 'familyA',
            'categories' => ['categoryA'],
            'values' => [
                'an_image' => [
                    [
                        'scope' => null,
                        'locale' => null,
                        'data' => $this->getFileInfoKey($this->getFixturePath('akeneo.jpg')),
                    ],
                ],
            ],
        ]);
        // export all products with media
        $this->get(SqlCreateJobInstance::class)->createJobInstance(
            [
                'code' => 'csv_product_export_with_media',
                'label' => 'csv_product_export_with_media',
                'job_name' => 'csv_product_export',
                'status' => 0,
                'type' => 'export',
                'raw_parameters' => 'a:13:{s:8:"filePath";s:15:"/tmp/export.csv";s:9:"delimiter";s:1:";";s:9:"enclosure";s:1:""";s:10:"withHeader";b:1;s:14:"user_to_notify";N;s:21:"is_user_authenticated";b:0;s:16:"decimalSeparator";s:1:".";s:10:"dateFormat";s:10:"yyyy-MM-dd";s:10:"with_media";b:1;s:10:"with_label";b:0;s:17:"header_with_label";b:0;s:11:"file_locale";N;s:7:"filters";a:2:{s:4:"data";a:3:{i:0;a:3:{s:5:"field";s:7:"enabled";s:8:"operator";s:1:"=";s:5:"value";b:1;}i:1;a:4:{s:5:"field";s:12:"completeness";s:8:"operator";s:3:"ALL";s:5:"value";i:100;s:7:"context";a:1:{s:7:"locales";a:1:{i:0;s:5:"en_US";}}}i:2;a:3:{s:5:"field";s:10:"categories";s:8:"operator";s:11:"IN CHILDREN";s:5:"value";a:1:{i:0;s:6:"master";}}}s:9:"structure";a:3:{s:5:"scope";s:9:"ecommerce";s:7:"locales";a:1:{i:0;s:5:"en_US";}s:10:"attributes";a:2:{i:0;s:8:"an_image";i:1;s:6:"a_file";}}}}',
            ]
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        if (\file_exists($this->tmpFile)) {
            @\unlink($this->tmpFile);
        }
    }

    private function createProduct(string $identifier, array $data): void
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        $this->get('pim_catalog.updater.product')->update($product, $data);

        $violations = $this->get('pim_catalog.validator.product')->validate($product);
        Assert::assertCount(0, $violations, \sprintf('The product is not valid: %s', $violations));
        $this->get('pim_catalog.saver.product')->save($product);
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
    }
}
