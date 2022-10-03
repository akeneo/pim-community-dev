<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Integration\UI\Web\Asset;

use Akeneo\AssetManager\Integration\ControllerIntegrationTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class UploadActionTest extends ControllerIntegrationTestCase
{
    private const ASSET_UPLOAD_ROUTE = 'akeneo_asset_manager_file_upload';

    /**
     * @test
     */
    public function it_uploads_an_asset_with_a_valid_filename(): void
    {
        $file = new UploadedFile(
            __DIR__ . '/../../../../../../back/Infrastructure/Symfony/Resources/fixtures/files/packshot.jpg',
            'packshot.jpg',
            null,
            null,
            true
        );

        $response = $this->uploadFile($file);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_doesnt_upload_an_asset_with_an_invalid_filename(): void
    {
        $file = new UploadedFile(
            __DIR__ . '/../../../../Common/TestFixtures/invalid­file.jpg',
            'invalid­file.jpg',
            null,
            null,
            true
        );

        $response = $this->uploadFile($file);
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    private function uploadFile(UploadedFile $uploadedFile): Response
    {
        $route = $this->get('router')->generate(self::ASSET_UPLOAD_ROUTE);

        $headers = [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
            'CONTENT_TYPE' => 'application/json',
        ];

        $this->client->request('POST', $route, [], ['file' => $uploadedFile], $headers);

        return $this->client->getResponse();
    }
}
