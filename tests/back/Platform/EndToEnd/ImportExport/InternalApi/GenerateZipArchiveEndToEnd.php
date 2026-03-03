<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\EndToEnd\ImportExport\InternalApi;

use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFileValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetIdentifierValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetImageValue;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\BatchBundle\Persistence\Sql\SqlCreateJobInstance;
use AkeneoTest\Platform\EndToEnd\InternalApiTestCase;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;

/**
 * Tests the zip archive generation when an export job generates more than 1 file
 */
class GenerateZipArchiveEndToEnd extends InternalApiTestCase
{
    private ?string $tmpFile = null;
    private int $adminId;

    /** @test */
    public function it_generates_a_zip_archive_when_an_export_job_generated_at_least_two_files()
    {
        $csv = $this->get('akeneo_integration_tests.launcher.job_launcher')->launchExport(
            'csv_product_export_with_media',
            'admin',
            []
        );
        Assert::assertSame(<<<CSV
            uuid;sku;categories;enabled;family;groups;an_image;a_file
            4168a79a-65b7-418f-b713-ac25b0291131;sku1;categoryA;1;familyA;;files/sku1/an_image/akeneo.png;files/sku1/a_file/akeneo.pdf
            9f987844-e0c9-4f89-80e0-bdedd597f888;sku2;categoryA;1;familyA;;files/sku2/an_image/akeneo.jpg;
            
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
        )->fetchOne();
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
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('admin');
        $this->adminId = $this->getUserId('admin');

        $this->createProduct('4168a79a-65b7-418f-b713-ac25b0291131', [
            new SetIdentifierValue('sku', 'sku1'),
            new SetFamily('familyA'),
            new SetCategories(['categoryA']),
            new SetImageValue('an_image', null, null, $this->getFileInfoKey($this->getFixturePath('akeneo.png'))),
            new SetFileValue('a_file', null, null, $this->getFileInfoKey($this->getFixturePath('akeneo.pdf'))),
        ]);
        $this->createProduct('9f987844-e0c9-4f89-80e0-bdedd597f888', [
            new SetIdentifierValue('sku', 'sku2'),
            new SetFamily('familyA'),
            new SetCategories(['categoryA']),
            new SetImageValue('an_image', null, null, $this->getFileInfoKey($this->getFixturePath('akeneo.jpg'))),
        ]);
        // export all products with media
        $this->get(SqlCreateJobInstance::class)->createJobInstance(
            [
                'code' => 'csv_product_export_with_media',
                'label' => 'csv_product_export_with_media',
                'job_name' => 'csv_product_export',
                'status' => 0,
                'type' => 'export',
                'raw_parameters' => 'a:14:{s:7:"storage";a:2:{s:4:"type";s:4:"none";s:9:"file_path";s:15:"/tmp/export.csv";}s:9:"delimiter";s:1:";";s:9:"enclosure";s:1:""";s:10:"withHeader";b:1;s:9:"with_uuid";b:1;s:15:"users_to_notify";a:0:{}s:21:"is_user_authenticated";b:0;s:16:"decimalSeparator";s:1:".";s:10:"dateFormat";s:10:"yyyy-MM-dd";s:10:"with_media";b:1;s:10:"with_label";b:0;s:17:"header_with_label";b:0;s:11:"file_locale";N;s:7:"filters";a:2:{s:4:"data";a:3:{i:0;a:3:{s:5:"field";s:7:"enabled";s:8:"operator";s:1:"=";s:5:"value";b:1;}i:1;a:4:{s:5:"field";s:12:"completeness";s:8:"operator";s:3:"ALL";s:5:"value";i:100;s:7:"context";a:1:{s:7:"locales";a:1:{i:0;s:5:"en_US";}}}i:2;a:3:{s:5:"field";s:10:"categories";s:8:"operator";s:11:"IN CHILDREN";s:5:"value";a:1:{i:0;s:6:"master";}}}s:9:"structure";a:3:{s:5:"scope";s:9:"ecommerce";s:7:"locales";a:1:{i:0;s:5:"en_US";}s:10:"attributes";a:2:{i:0;s:8:"an_image";i:1;s:6:"a_file";}}}}',
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

    private function createProduct(string $uuid, array $userIntents): void
    {
        $this->get('pim_enrich.product.message_bus')->dispatch(
            UpsertProductCommand::createWithUuid(
                $this->adminId,
                ProductUuid::fromUuid(Uuid::fromString($uuid)),
                $userIntents
            )
        );
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
    }
}
