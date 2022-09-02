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
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class UploadStructureFileActionTest extends ControllerIntegrationTestCase
{
    private const ROUTE = 'pimee_tailored_import_upload_structure_file_action';
    private WebClientHelper $webClientHelper;
    private FilesystemProvider $filesystemProvider;

    public function setUp(): void
    {
        parent::setUp();

        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('julia', $this->client);
        $this->webClientHelper = $this->get('akeneo_integration_tests.helper.web_client');
        $this->filesystemProvider = $this->get('akeneo_file_storage.file_storage.filesystem_provider');
    }

    public function test_it_uploads_a_file(): void
    {
        $fileToUpload = new UploadedFile(
            __DIR__ . '/../../../Common/simple_import.xlsx',
            'simple_import.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        );

        $response = $this->uploadFile($fileToUpload);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $fileInfo = \json_decode($response->getContent(), true);
        $this->assertFileIsStored($fileInfo['filePath']);
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
        return $this->catalog->useMinimalCatalog();
    }
}
