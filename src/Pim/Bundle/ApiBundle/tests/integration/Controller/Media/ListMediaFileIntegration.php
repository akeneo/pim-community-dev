<?php

namespace Pim\Bundle\ApiBundle\tests\integration\Controller\Media;

use Akeneo\Test\Integration\MediaSanitizer;
use Symfony\Component\HttpFoundation\Response;

class ListMediaFileIntegration extends AbstractMediaFileTestCase
{
    public function setUp()
    {
        parent::setUp();

        if (1 === self::$count || $this->getConfiguration()->isDatabasePurgedForEachTest()) {
            $this->createMedia(new \SplFileInfo($this->getFixturePath('akeneo.jpg')));
            $this->createMedia(new \SplFileInfo($this->getFixturePath('ziggy.png')));
            $this->createMedia(new \SplFileInfo($this->getFixturePath('akeneo.txt')));
        }
    }

    public function testListMediaFiles()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/media_files');

        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href" : "http://localhost/api/rest/v1/media_files?page=1&limit=10"},
        "first" : {"href" : "http://localhost/api/rest/v1/media_files?page=1&limit=10"},
        "last"  : {"href" : "http://localhost/api/rest/v1/media_files?page=1&limit=10"}
    },
    "current_page": 1,
    "pages_count": 1,
    "items_count": 3,
    "_embedded": {
        "items" : [
            {
                "_links": {
                    "self": {"href": "http://localhost/api/rest/v1/media_files/8/6/d/5/86d5d517bdff522f16ce60b24e06086db0af85f9_akeneo.jpg"},
                    "download": {"href": "http://localhost/api/rest/v1/media_files/8/6/d/5/86d5d517bdff522f16ce60b24e06086db0af85f9_akeneo.jpg/download"}
                },
                "code": "8/6/d/5/86d5d517bdff522f16ce60b24e06086db0af85f9_akeneo.jpg",
                "original_filename": "akeneo.jpg",
                "mime_type": "image/jpeg",
                "size": 10584,
                "extension": "jpg"
            },
            {
                "_links": {
                    "self": {"href": "http://localhost/api/rest/v1/media_files/c/7/a/b/c7abf5208285b90875a8621448598f42ff3489eb_ziggy.png"},
                    "download": {"href": "http://localhost/api/rest/v1/media_files/c/7/a/b/c7abf5208285b90875a8621448598f42ff3489eb_ziggy.png/download"}
                },
                "code": "c/7/a/b/c7abf5208285b90875a8621448598f42ff3489eb_ziggy.png",
                "original_filename": "ziggy.png",
                "mime_type": "image/png",
                "size": 100855,
                "extension": "png"
            },
            {
                "_links": {
                    "self": {"href": "http://localhost/api/rest/v1/media_files/d/d/1/2/dd12d8e12d6de43fe7c424d6ed0b45cc0aa6e20f_akeneo.txt"},
                    "download": {"href": "http://localhost/api/rest/v1/media_files/d/d/1/2/dd12d8e12d6de43fe7c424d6ed0b45cc0aa6e20f_akeneo.txt/download"}
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
        $this->assertJsonStringEqualsJsonString(
            $this->sanitizeContent($expected),
            $this->sanitizeContent($response->getContent())
        );
    }

    public function testOutOfRangeListMediaFiles()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/media_files?page=3&limit=2');

        $expected = <<<JSON
{
    "_links"       : {
        "self": {"href" : "http://localhost/api/rest/v1/media_files?page=3&limit=2"},
        "first": {"href" : "http://localhost/api/rest/v1/media_files?page=1&limit=2"},
        "last": {"href" : "http://localhost/api/rest/v1/media_files?page=2&limit=2"}
    },
    "current_page" : 3,
    "pages_count"  : 2,
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

    public function testPaginationListOfChannels()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/media_files?page=2&limit=2');

        $expected = <<<JSON
{
    "_links"       : {
        "self": {"href" : "http://localhost/api/rest/v1/media_files?page=2&limit=2"},
        "first": {"href" : "http://localhost/api/rest/v1/media_files?page=1&limit=2"},
        "last": {"href" : "http://localhost/api/rest/v1/media_files?page=2&limit=2"},
        "previous": {"href" : "http://localhost/api/rest/v1/media_files?page=1&limit=2"}
    },
    "current_page" : 2,
    "pages_count"  : 2,
    "items_count"  : 3,
    "_embedded"    : {
        "items" : [
            {
                "_links": {
                    "self": {"href": "http://localhost/api/rest/v1/media_files/d/d/1/2/dd12d8e12d6de43fe7c424d6ed0b45cc0aa6e20f_akeneo.txt"},
                    "download": {"href": "http://localhost/api/rest/v1/media_files/d/d/1/2/dd12d8e12d6de43fe7c424d6ed0b45cc0aa6e20f_akeneo.txt/download"}
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
        $this->assertJsonStringEqualsJsonString(
            $this->sanitizeContent($expected),
            $this->sanitizeContent($response->getContent())
        );
    }

    /**
     * @param string $data
     *
     * @return string
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

        return json_encode($data);
    }
}
