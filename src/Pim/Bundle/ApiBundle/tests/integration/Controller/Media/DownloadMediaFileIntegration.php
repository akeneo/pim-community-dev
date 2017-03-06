<?php

namespace Pim\Bundle\ApiBundle\tests\integration\Controller\MediaFile;

use Pim\Bundle\ApiBundle\tests\integration\Controller\Media\AbstractMediaFileTestCase;
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
        ob_start(function ($streamFile) use (&$contentFile) {
            $contentFile.= $streamFile;

            return '';
        });

        $client->request('GET', 'api/rest/v1/media_files/' . $media->getKey() . '/download');
        ob_end_clean();

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame('attachment; filename="akeneo.jpg"', $response->headers->get('content-disposition'));
        $this->assertSame('application/octet-stream', $response->headers->get('content-type'));
        $this->assertEquals($contentFile, file_get_contents($this->getFixturePath('akeneo.jpg')));
    }

    public function testNotFoundAMediaFile()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/media_files/not_found/download');

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
}
