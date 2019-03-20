<?php

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Category\ExternalApi;

use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class ListCategoryEndToEnd extends ApiTestCase
{
    /**
     * @group critical
     */
    public function testListCategories()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/categories');

        $expected = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/categories?page=1&limit=10&with_count=false"
        },
        "first": {
            "href": "http://localhost/api/rest/v1/categories?page=1&limit=10&with_count=false"
        }
    },
    "current_page": 1,
    "_embedded": {
        "items": [
            {
                "_links": {
                    "self": {
                        "href": "http://localhost/api/rest/v1/categories/master"
                    }
                },
                "code": "master",
                "parent": null,
                "labels": {}
            },
            {
                "_links": {
                    "self": {
                        "href": "http://localhost/api/rest/v1/categories/categoryA"
                    }
                },
                "code": "categoryA",
                "parent": "master",
                "labels": {
                    "en_US": "Category A",
                    "fr_FR": "Catégorie A"
                }
            },
            {
                "_links": {
                    "self": {
                        "href": "http://localhost/api/rest/v1/categories/categoryA1"
                    }
                },
                "code": "categoryA1",
                "parent": "categoryA",
                "labels": {}
            },
            {
                "_links": {
                    "self": {
                        "href": "http://localhost/api/rest/v1/categories/categoryA2"
                    }
                },
                "code": "categoryA2",
                "parent": "categoryA",
                "labels": {}
            },
            {
                "_links": {
                    "self": {
                        "href": "http://localhost/api/rest/v1/categories/categoryB"
                    }
                },
                "code": "categoryB",
                "parent": "master",
                "labels": {}
            },
            {
                "_links": {
                    "self": {
                        "href": "http://localhost/api/rest/v1/categories/categoryC"
                    }
                },
                "code": "categoryC",
                "parent": "master",
                "labels": {}
            },
            {
                "_links": {
                    "self": {
                        "href": "http://localhost/api/rest/v1/categories/master_china"
                    }
                },
                "code": "master_china",
                "parent": null,
                "labels": {}
            }
        ]
    }
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testListCategoriesWithCount()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/categories?with_count=true');

        $expected = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/categories?page=1&limit=10&with_count=true"
        },
        "first": {
            "href": "http://localhost/api/rest/v1/categories?page=1&limit=10&with_count=true"
        }
    },
    "current_page": 1,
    "items_count": 7,
    "_embedded": {
        "items": [
            {
                "_links": {
                    "self": {
                        "href": "http://localhost/api/rest/v1/categories/master"
                    }
                },
                "code": "master",
                "parent": null,
                "labels": {}
            },
            {
                "_links": {
                    "self": {
                        "href": "http://localhost/api/rest/v1/categories/categoryA"
                    }
                },
                "code": "categoryA",
                "parent": "master",
                "labels": {
                    "en_US": "Category A",
                    "fr_FR": "Catégorie A"
                }
            },
            {
                "_links": {
                    "self": {
                        "href": "http://localhost/api/rest/v1/categories/categoryA1"
                    }
                },
                "code": "categoryA1",
                "parent": "categoryA",
                "labels": {}
            },
            {
                "_links": {
                    "self": {
                        "href": "http://localhost/api/rest/v1/categories/categoryA2"
                    }
                },
                "code": "categoryA2",
                "parent": "categoryA",
                "labels": {}
            },
            {
                "_links": {
                    "self": {
                        "href": "http://localhost/api/rest/v1/categories/categoryB"
                    }
                },
                "code": "categoryB",
                "parent": "master",
                "labels": {}
            },
            {
                "_links": {
                    "self": {
                        "href": "http://localhost/api/rest/v1/categories/categoryC"
                    }
                },
                "code": "categoryC",
                "parent": "master",
                "labels": {}
            },
            {
                "_links": {
                    "self": {
                        "href": "http://localhost/api/rest/v1/categories/master_china"
                    }
                },
                "code": "master_china",
                "parent": null,
                "labels": {}
            }
        ]
    }
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testOutOfRangeListCategories()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/categories?limit=10&page=2');


        $expected = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/categories?page=2&limit=10&with_count=false"
        },
        "first": {
            "href": "http://localhost/api/rest/v1/categories?page=1&limit=10&with_count=false"
        },
        "previous": {
            "href": "http://localhost/api/rest/v1/categories?page=1&limit=10&with_count=false"
        }
    },
    "current_page": 2,
    "_embedded": {
        "items": []
    }
}
JSON;
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
