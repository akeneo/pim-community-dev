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

namespace Akeneo\Platform\TailoredImport\Test\Integration\Infrastructure\Controller\SampleData;

use Akeneo\Platform\TailoredImport\Domain\Model\Filesystem\Storage;
use Akeneo\Platform\TailoredImport\Test\Integration\ControllerIntegrationTestCase;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\IntegrationTestsBundle\Helper\WebClientHelper;
use Akeneo\Tool\Component\FileStorage\File\FileStorer;
use Symfony\Component\HttpFoundation\Response;

class GetRefreshedSampleDataActionTest extends ControllerIntegrationTestCase
{
    private const ROUTE = 'pimee_tailored_import_get_refreshed_sample_data_action';
    private WebClientHelper $webClientHelper;
    private FileStorer $fileStorer;

    public function setUp(): void
    {
        parent::setUp();

        $this->get('akeneo_integration_tests.helper.authenticator')->logIn($this->client, 'julia');
        $this->webClientHelper = $this->get('akeneo_integration_tests.helper.web_client');
        $this->fileStorer = $this->get('akeneo_file_storage.file_storage.file.file_storer');
    }

    public function test_it_returns_a_refreshed_sample_data(): void
    {
        $fileKey = $this->storeFile(__DIR__ . '/../../../../Common/simple_import.xlsx');
        $this->webClientHelper->callApiRoute(
            $this->client,
            self::ROUTE,
            [],
            'GET',
            [
                'current_sample' => ['Produit 1', 'Produit 2', 'Produit 3', 'ref1', 'ref2'],
                'file_key' => $fileKey,
                'column_indices' => [0, 1],
                'sheet_name' => 'Products',
                'product_line' => 2,
            ],
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $response = \json_decode($response->getContent(), true);

        $this->assertSame(['refreshed_data' => 'ref3'], $response);
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
