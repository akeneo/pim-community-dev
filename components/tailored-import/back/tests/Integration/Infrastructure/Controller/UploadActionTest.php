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

use Akeneo\Platform\TailoredImport\Domain\Filesystem\Storage;
use Akeneo\Platform\TailoredImport\Test\Integration\ControllerIntegrationTestCase;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\IntegrationTestsBundle\Helper\WebClientHelper;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class UploadActionTest extends ControllerIntegrationTestCase
{
    private const ROUTE = 'pimee_tailored_import_upload_action';
    private WebClientHelper $webClientHelper;
    private FilesystemProvider $filesystemProvider;

    public function setUp(): void
    {
        parent::setUp();

        $this->get('akeneo_integration_tests.helper.authenticator')->logIn($this->client, 'julia');
        $this->webClientHelper = $this->get('akeneo_integration_tests.helper.web_client');
        $this->filesystemProvider = $this->get('akeneo_file_storage.file_storage.filesystem_provider');
    }

    public function test_it_uploads_a_file(): void
    {
        $fileToUpload = new UploadedFile(
            __DIR__ . '/../../../Common/simple_import.xlsx',
            'simple_import.xlsx',
        );

        $response = $this->uploadFile($fileToUpload);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $fileInfo = \json_decode($response->getContent(), true);
        $this->assertSame('simple_import.xlsx', $fileInfo['original_filename']);
        $this->assertFileIsStored($fileInfo['file_key']);
    }

    private function uploadFile(UploadedFile $uploadedFile): Response
    {
        $route = $this->get('router')->generate(self::ROUTE);

        $headers = [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
            'CONTENT_TYPE' => 'application/json',
        ];

        $this->client->request('POST', $route, [], ['file' => $uploadedFile], $headers);

        $response = $this->client->getResponse();

        return $response;
    }

    private function assertFileIsStored(string $fileKey): void
    {
        $fileStorage = $this->filesystemProvider->getFilesystem(Storage::FILE_STORAGE_ALIAS);
        $this->assertTrue($fileStorage->fileExists($fileKey));
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }
}
