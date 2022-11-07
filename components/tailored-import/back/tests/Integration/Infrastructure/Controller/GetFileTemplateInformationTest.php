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

class GetFileTemplateInformationTest extends ControllerIntegrationTestCase
{
    private const ROUTE = 'pimee_tailored_import_get_file_template_information_action';
    private WebClientHelper $webClientHelper;
    private FileStorer $fileStorer;

    public function setUp(): void
    {
        parent::setUp();

        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('julia', $this->client);
        $this->webClientHelper = $this->get('akeneo_integration_tests.helper.web_client');
        $this->fileStorer = $this->get('akeneo_file_storage.file_storage.file.file_storer');
    }

    public function test_it_return_template_information_from_a_file(): void
    {
        $fileKey = $this->storeFile(__DIR__ . '/../../../Common/simple_import.xlsx');
        $this->webClientHelper->callApiRoute(
            $this->client,
            self::ROUTE,
            [],
            'GET',
            [
                'file_key' => $fileKey,
                'sheet_name' => 'Products',
            ],
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $response = \json_decode($response->getContent(), true);
        $expectedColumnLabels = [
            'sheet_names' => [
                'Products',
                'Empty lines and columns',
                'Empty sheet',
                'Out of bound value',
                'Empty header',
                'Two lines',
                'Trailing empty header',
                'More than 500 cols',
            ],
            'rows' => [
                ['Sku', 'Name', 'Price', 'Enabled', 'Release date', 'Price with tax'],
                ['ref1', 'Produit 1', '12', 'TRUE', '3/22/2022', '14.4'],
                ['ref2','Produit 2','13.87','FALSE','5/23/2022', ''],
                ['ref3','Produit 3','16','TRUE','10/5/2015','19.2'],
            ],
            'column_count' => 6
        ];

        $this->assertSame($expectedColumnLabels, $response);
    }

    public function test_it_returns_a_bad_request_when_file_key_is_invalid(): void
    {
        $this->webClientHelper->callApiRoute(
            $this->client,
            self::ROUTE,
            [],
            'GET',
            [
                'file_key' => 'invalid_key_file',
                'sheet_name' => 'Products',
            ],
        );

        $response = $this->client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function test_it_returns_a_bad_request_when_sheet_name_is_not_found(): void
    {
        $fileKey = $this->storeFile(__DIR__ . '/../../../Common/simple_import.xlsx');
        $this->webClientHelper->callApiRoute(
            $this->client,
            self::ROUTE,
            [],
            'GET',
            [
                'file_key' => $fileKey,
                'sheet_name' => 'Sheet not found',
            ],
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
