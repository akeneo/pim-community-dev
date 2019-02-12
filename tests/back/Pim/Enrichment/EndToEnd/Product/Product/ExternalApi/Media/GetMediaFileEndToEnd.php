<?php

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\ExternalApi\MediaFile;

use Akeneo\Test\IntegrationTestsBundle\Sanitizer\MediaSanitizer;
use AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\ExternalApi\Media\AbstractMediaFileTestCase;
use Symfony\Component\HttpFoundation\Response;

class GetMediaFileEndToEnd extends AbstractMediaFileTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->createMedia(new \SplFileInfo($this->getFixturePath('akeneo.jpg')));
    }

    public function testGetAMediaFile()
    {
        $client = $this->createAuthenticatedClient();

        $media = $this->get('pim_api.repository.media_file')->findOneBy(['originalFilename' => 'akeneo.jpg']);

        $client->request('GET', '/api/rest/v1/media-files/' . $media->getKey());

        $expected = <<<JSON
{
    "code": "8/6/d/5/86d5d517bdff522f16ce60b24e06086db0af85f9_akeneo.jpg",
    "original_filename": "akeneo.jpg",
    "mime_type": "image/jpeg",
    "size": 10584,
    "extension": "jpg",
    "_links": {
        "download": {"href": "http://localhost/api/rest/v1/media-files/8/6/d/5/86d5d517bdff522f16ce60b24e06086db0af85f9_akeneo.jpg/download"}
    }
}
JSON;

        $response = $client->getResponse();
        $expected = json_decode($expected, true);
        $result = json_decode($response->getContent(), true);

        $expected['code'] = MediaSanitizer::sanitize($expected['code']);
        $expected['_links']['download']['href'] = MediaSanitizer::sanitize($expected['_links']['download']['href']);
        $result['code'] = MediaSanitizer::sanitize($result['code']);
        $result['_links']['download']['href'] = MediaSanitizer::sanitize($result['_links']['download']['href']);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame($expected, $result);
    }

    public function testMediaFileNotFound()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/media-files/not_found');

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
