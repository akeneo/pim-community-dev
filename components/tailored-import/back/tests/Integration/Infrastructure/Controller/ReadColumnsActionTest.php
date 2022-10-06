<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Test\Integration\Infrastructure\Controller;

use Akeneo\Platform\TailoredImport\Domain\Model\Filesystem\Storage;
use Akeneo\Platform\TailoredImport\Test\Integration\ControllerIntegrationTestCase;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\IntegrationTestsBundle\Helper\WebClientHelper;
use Akeneo\Tool\Component\FileStorage\File\FileStorer;
use Symfony\Component\HttpFoundation\Response;

class ReadColumnsActionTest extends ControllerIntegrationTestCase
{
    private const ROUTE = 'pimee_tailored_import_read_columns_action';
    private WebClientHelper $webClientHelper;
    private FileStorer $fileStorer;

    public function setUp(): void
    {
        parent::setUp();

        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('julia', $this->client);
        $this->webClientHelper = $this->get('akeneo_integration_tests.helper.web_client');
        $this->fileStorer = $this->get('akeneo_file_storage.file_storage.file.file_storer');
    }

    public function test_it_reads_columns_from_a_file(): void
    {
        $fileKey = $this->storeFile(__DIR__ . '/../../../Common/simple_import.xlsx');
        $fileStructure = [
            'header_row' => 1,
            'first_column' => 0,
            'first_product_row' => 2,
            'unique_identifier_column' => 1,
            'sheet_name' => 'Products',
        ];

        $this->webClientHelper->callApiRoute(
            $this->client,
            self::ROUTE,
            [],
            'POST',
            [],
            json_encode([
                'file_key' => $fileKey,
                'file_structure' => $fileStructure,
            ]),
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $columns = \json_decode($response->getContent(), true);
        $expectedColumnLabels = [
            'Sku',
            'Name',
            'Price',
            'Enabled',
            'Release date',
            'Price with tax',
        ];
        $this->assertSame($expectedColumnLabels, array_column($columns, 'label'));
    }

    public function test_it_returns_a_bad_request_when_file_key_is_missing(): void
    {
        $fileStructure = [
            'header_row' => 1,
            'first_column' => 0,
            'first_product_row' => 2,
            'unique_identifier_column' => 1,
            'sheet_name' => 'Products',
        ];

        $this->webClientHelper->callApiRoute(
            $this->client,
            self::ROUTE,
            [],
            'POST',
            [],
            json_encode([
                'file_structure' => $fileStructure,
            ]),
        );

        $response = $this->client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    private function storeFile(string $filePath): string
    {
        $file = new \SplFileInfo($filePath);
        $fileInfo = $this->fileStorer->store($file, Storage::FILE_STORAGE_ALIAS);

        return $fileInfo->getKey();
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
