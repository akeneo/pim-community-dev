<?php

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\ExternalApi\Media;

use Akeneo\Test\IntegrationTestsBundle\Sanitizer\MediaSanitizer;
use Symfony\Component\HttpFoundation\Response;

class ListMediaFileEndToEnd extends AbstractMediaFileTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->createMedia(new \SplFileInfo($this->getFixturePath('akeneo.jpg')));
        $this->createMedia(new \SplFileInfo($this->getFixturePath('ziggy.png')));
        $this->createMedia(new \SplFileInfo($this->getFixturePath('akeneo.txt')));
    }

    /**
     * @group critical
     */
    public function testListMediaFiles()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/media-files');

        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href" : "http://localhost/api/rest/v1/media-files?page=1&limit=10&with_count=false"},
        "first" : {"href" : "http://localhost/api/rest/v1/media-files?page=1&limit=10&with_count=false"}
    },
    "current_page": 1,
    "_embedded": {
        "items" : [
            {
                "_links": {
                    "self": {"href": "http://localhost/api/rest/v1/media-files/8/6/d/5/86d5d517bdff522f16ce60b24e06086db0af85f9_akeneo.jpg"},
                    "download": {"href": "http://localhost/api/rest/v1/media-files/8/6/d/5/86d5d517bdff522f16ce60b24e06086db0af85f9_akeneo.jpg/download"}
                },
                "code": "8/6/d/5/86d5d517bdff522f16ce60b24e06086db0af85f9_akeneo.jpg",
                "original_filename": "akeneo.jpg",
                "mime_type": "image/jpeg",
                "size": 10584,
                "extension": "jpg"
            },
            {
                "_links": {
                    "self": {"href": "http://localhost/api/rest/v1/media-files/c/7/a/b/c7abf5208285b90875a8621448598f42ff3489eb_ziggy.png"},
                    "download": {"href": "http://localhost/api/rest/v1/media-files/c/7/a/b/c7abf5208285b90875a8621448598f42ff3489eb_ziggy.png/download"}
                },
                "code": "c/7/a/b/c7abf5208285b90875a8621448598f42ff3489eb_ziggy.png",
                "original_filename": "ziggy.png",
                "mime_type": "image/png",
                "size": 100855,
                "extension": "png"
            },
            {
                "_links": {
                    "self": {"href": "http://localhost/api/rest/v1/media-files/d/d/1/2/dd12d8e12d6de43fe7c424d6ed0b45cc0aa6e20f_akeneo.txt"},
                    "download": {"href": "http://localhost/api/rest/v1/media-files/d/d/1/2/dd12d8e12d6de43fe7c424d6ed0b45cc0aa6e20f_akeneo.txt/download"}
                },
                "code": "d/d/1/2/dd12d8e12d6de43fe7c424d6ed0b45cc0aa6e20f_akeneo.txt",
                "original_filename": "akeneo.txt",
                "mime_type": "text/plain",
                "size": 32,
                "extension": "txt"
            }
        ]
    }
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame(
            $this->sanitizeContent($expected),
            $this->sanitizeContent($response->getContent())
        );
    }

    public function testOutOfRangeListMediaFiles()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/media-files?page=3&limit=2');

        $expected = <<<JSON
{
    "_links"       : {
        "self": {"href" : "http://localhost/api/rest/v1/media-files?page=3&limit=2&with_count=false"},
        "first": {"href" : "http://localhost/api/rest/v1/media-files?page=1&limit=2&with_count=false"},
        "previous": {"href" : "http://localhost/api/rest/v1/media-files?page=2&limit=2&with_count=false"}
    },
    "current_page" : 3,
    "_embedded"    : {
        "items" : []
    }
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testListMediaFilesWithCount()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/media-files?page=3&limit=2&with_count=true');

        $expected = <<<JSON
{
    "_links"       : {
        "self": {"href" : "http://localhost/api/rest/v1/media-files?page=3&limit=2&with_count=true"},
        "first": {"href" : "http://localhost/api/rest/v1/media-files?page=1&limit=2&with_count=true"},
        "previous": {"href" : "http://localhost/api/rest/v1/media-files?page=2&limit=2&with_count=true"}
    },
    "current_page" : 3,
    "items_count"  : 3,
    "_embedded"    : {
        "items" : []
    }
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    /**
     * @group critical
     */
    public function testPaginatedListOfMediaFiles()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/media-files?page=2&limit=2');

        $expected = <<<JSON
{
    "_links"       : {
        "self": {"href" : "http://localhost/api/rest/v1/media-files?page=2&limit=2&with_count=false"},
        "first": {"href" : "http://localhost/api/rest/v1/media-files?page=1&limit=2&with_count=false"},
        "previous": {"href" : "http://localhost/api/rest/v1/media-files?page=1&limit=2&with_count=false"}
    },
    "current_page" : 2,
    "_embedded"    : {
        "items" : [
            {
                "_links": {
                    "self": {"href": "http://localhost/api/rest/v1/media-files/d/d/1/2/dd12d8e12d6de43fe7c424d6ed0b45cc0aa6e20f_akeneo.txt"},
                    "download": {"href": "http://localhost/api/rest/v1/media-files/d/d/1/2/dd12d8e12d6de43fe7c424d6ed0b45cc0aa6e20f_akeneo.txt/download"}
                },
                "code": "d/d/1/2/dd12d8e12d6de43fe7c424d6ed0b45cc0aa6e20f_akeneo.txt",
                "original_filename": "akeneo.txt",
                "mime_type": "text/plain",
                "size": 32,
                "extension": "txt"
            }
        ]
    }
}
JSON;
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame(
            $this->sanitizeContent($expected),
            $this->sanitizeContent($response->getContent())
        );
    }

    /**
     * @param string $data
     *
     * @return array
     */
    protected function sanitizeContent($data)
    {
        $data = json_decode($data, true);

        foreach ($data['_embedded']['items'] as $index => $item) {
            foreach ($item['_links'] as $rel => $link) {
                $data['_embedded']['items'][$index]['_links'][$rel] = MediaSanitizer::sanitize($link['href']);
            }
            $data['_embedded']['items'][$index]['code'] = MediaSanitizer::sanitize($item['code']);
        }

        return $data;
    }
}
