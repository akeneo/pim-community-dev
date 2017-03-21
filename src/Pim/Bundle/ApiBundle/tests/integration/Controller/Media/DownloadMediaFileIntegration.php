<?php

namespace Pim\Bundle\ApiBundle\tests\integration\Controller\MediaFile;

use Akeneo\Component\FileStorage\Model\FileInfo;
use Pim\Bundle\ApiBundle\tests\integration\Controller\Media\AbstractMediaFileTestCase;
use Pim\Component\Catalog\FileStorage;
use Symfony\Component\HttpFoundation\Response;

class DownloadMediaFileIntegration extends AbstractMediaFileTestCase
{
    public function setUp()
    {
        parent::setUp();

        if (1 === self::$count || $this->getConfiguration()->isDatabasePurgedForEachTest()) {
            $this->createMedia(new \SplFileInfo($this->getFixturePath('akeneo.jpg')));
        }
    }

    public function testDownloadAMediaFile()
    {
        $client = $this->createAuthenticatedClient();

        $media = $this->get('pim_api.repository.media_file')->findOneBy(['originalFilename' => 'akeneo.jpg']);

        $contentFile = '';
        ob_start(function ($streamedFile) use (&$contentFile) {
            $contentFile .= $streamedFile;

            return '';
        });

        $client->request('GET', '/api/rest/v1/media-files/' . $media->getKey() . '/download');
        ob_end_clean();

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame('attachment; filename="akeneo.jpg"', $response->headers->get('content-disposition'));
        $this->assertSame('image/jpeg', $response->headers->get('content-type'));
        $this->assertEquals($contentFile, file_get_contents($this->getFixturePath('akeneo.jpg')));
    }

    public function testMediaFileNotFound()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/media-files/not_found/download');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        $expected = <<<JSON
{
    "code": 404,
    "message": "Media file \"not_found\" does not exist."
}
JSON;

        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testMediaFileFoundButRemovedOnFilesystem()
    {
        $client = $this->createAuthenticatedClient();

        $fileInfo = new FileInfo();
        $fileInfo->setKey('my_file');
        $fileInfo->setStorage(FileStorage::CATALOG_STORAGE_ALIAS);
        $fileInfo->setOriginalFilename('new file');
        $fileInfo->setMimeType('text/plain');
        $fileInfo->setSize(1);
        $fileInfo->setExtension('txt');
        $this->get('akeneo_file_storage.saver.file')->save($fileInfo);

        $client->request('GET', '/api/rest/v1/media-files/my_file/download');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        $expected = <<<JSON
{
    "code": 404,
    "message": "Media file \"my_file\" is not present on the filesystem."
}
JSON;

        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }
}
