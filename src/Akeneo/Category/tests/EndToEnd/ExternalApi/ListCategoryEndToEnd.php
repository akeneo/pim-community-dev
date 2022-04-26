<?php

namespace Akeneo\Test\Category\EndToEnd\ExternalApi;

use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use AkeneoTest\Pim\Enrichment\Integration\Normalizer\NormalizedCategoryCleaner;
use Symfony\Component\HttpFoundation\Response;

class ListCategoryEndToEnd extends ApiTestCase
{
    /**
     * @group critical
     */
    public function testListAllPaginatedCategories()
    {
        $categories = $this->getStandardizedCategories();
        $firstPageClient = $this->createAuthenticatedClient();
        $firstPageClient->request('GET', 'api/rest/v1/categories?limit=4&page=1');

        $secondPageClient = $this->createAuthenticatedClient();
        $secondPageClient->request('GET', 'api/rest/v1/categories?limit=4&page=2');

        $expectedFirstPage = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/categories?page=1&limit=4&with_count=false"
        },
        "first": {
            "href": "http://localhost/api/rest/v1/categories?page=1&limit=4&with_count=false"
        },
        "next": {
            "href": "http://localhost/api/rest/v1/categories?page=2&limit=4&with_count=false"
        }
    },
    "current_page": 1,
    "_embedded": {
        "items": [
            {$categories['master']},
            {$categories['categoryA']},
            {$categories['categoryA1']},
            {$categories['categoryA2']}
        ]
    }
}
JSON;
        $expectedSecondPage = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/categories?page=2&limit=4&with_count=false"
        },
        "first": {
            "href": "http://localhost/api/rest/v1/categories?page=1&limit=4&with_count=false"
        },
        "previous": {
            "href": "http://localhost/api/rest/v1/categories?page=1&limit=4&with_count=false"
        }
    },
    "current_page": 2,
    "_embedded": {
        "items": [
            {$categories['categoryB']},
            {$categories['categoryC']},
            {$categories['master_china']}
        ]
    }
}
JSON;

        $this->assertSameResponse($expectedFirstPage, $firstPageClient->getResponse());
        $this->assertSameResponse($expectedSecondPage, $secondPageClient->getResponse());
    }

    /**
     * @param array $data
     *
     * @return CategoryInterface
     */
    protected function createCategory(array $data = []): CategoryInterface
    {
        $category = $this->get('pim_catalog.factory.category')->create();
        $this->get('pim_catalog.updater.category')->update($category, $data);
        $this->get('validator')->validate($category);
        $this->get('pim_catalog.saver.category')->save($category);

        return $category;
    }

    public function testListCategoriesByParent()
    {
        $this->createCategory(['parent' => 'categoryA1', 'code' => 'categoryA1-1']);
        $this->createCategory(['parent' => 'categoryA1-1', 'code' => 'categoryA1-1-1']);

        $categories = $this->getStandardizedCategories();
        $search = '{"parent":[{"operator":"=","value":"categoryA"}]}';
        $searchEncoded = $this->encodeStringWithSymfonyUrlGeneratorCompatibility($search);

        $client = $this->createAuthenticatedClient();
        $client->request('GET', 'api/rest/v1/categories?with_count=true&search=' . $search);

        $expected = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/categories?page=1&limit=10&with_count=true&search={$searchEncoded}"
        },
        "first": {
            "href": "http://localhost/api/rest/v1/categories?page=1&limit=10&with_count=true&search={$searchEncoded}"
        }
    },
    "current_page": 1,
    "items_count": 4,
    "_embedded": {
        "items": [
            {$categories['categoryA1']},
            {
                "_links": {
                    "self": {
                        "href": "http://localhost/api/rest/v1/categories/categoryA1-1"
                    }
                },
                "code": "categoryA1-1",
                "parent": "categoryA1",
                "updated" : "2016-06-14T13:12:50+02:00",
                "labels": {}
            },
            {
                "_links": {
                    "self": {
                        "href": "http://localhost/api/rest/v1/categories/categoryA1-1-1"
                    }
                },
                "code": "categoryA1-1-1",
                "parent": "categoryA1-1",
                "updated" : "2016-06-14T13:12:50+02:00",
                "labels": {}
            },
            {$categories['categoryA2']}
        ]
    }
}
JSON;

        $this->assertSameResponse($expected, $client->getResponse());
    }

    public function testListCategoriesWithCount()
    {
        $categories = $this->getStandardizedCategories();
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
            {$categories['master']},
            {$categories['categoryA']},
            {$categories['categoryA1']},
            {$categories['categoryA2']},
            {$categories['categoryB']},
            {$categories['categoryC']},
            {$categories['master_china']}
        ]
    }
}
JSON;

        $this->assertSameResponse($expected, $client->getResponse());
    }

    public function testListCategoriesByCodes()
    {
        $categories = $this->getStandardizedCategories();
        $search = '{"code":[{"operator":"IN","value":["master","categoryA2","master_china"]}]}';
        $searchEncoded = $this->encodeStringWithSymfonyUrlGeneratorCompatibility($search);

        $client = $this->createAuthenticatedClient();
        $client->request('GET', 'api/rest/v1/categories?limit=5&page=1&with_count=true&search=' . $search);

        $expected = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/categories?page=1&limit=5&with_count=true&search={$searchEncoded}"
        },
        "first": {
            "href": "http://localhost/api/rest/v1/categories?page=1&limit=5&with_count=true&search={$searchEncoded}"
        }
    },
    "current_page": 1,
    "items_count": 3,
    "_embedded": {
        "items": [
            {$categories['master']},
            {$categories['categoryA2']},
            {$categories['master_china']}
        ]
    }
}
JSON;

        $this->assertSameResponse($expected, $client->getResponse());
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


    public function testListCategoriesWithPosition()
    {
        $categories = $this->getStandardizedCategorieswithPositionInformation();
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/categories?with_position=true');

        $expected = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/categories?page=1&limit=10&with_count=false&with_position=true"
        },
        "first": {
            "href": "http://localhost/api/rest/v1/categories?page=1&limit=10&with_count=false&with_position=true"
        }
    },
    "current_page": 1,
    "_embedded": {
        "items": [
            {$categories['master']},
            {$categories['categoryA']},
            {$categories['categoryA1']},
            {$categories['categoryA2']},
            {$categories['categoryB']},
            {$categories['categoryC']},
            {$categories['master_china']}
        ]
    }
}
JSON;

        $this->assertSameResponse($expected, $client->getResponse());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    public function getStandardizedCategories(): array
    {
        $categories['master'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/categories/master"
        }
    },
    "code": "master",
    "parent": null,
    "updated" : "2016-06-14T13:12:50+02:00",
    "labels": {}
}
JSON;
        $categories['categoryA'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/categories/categoryA"
        }
    },
    "code": "categoryA",
    "parent": "master",
    "updated" : "2016-06-14T13:12:50+02:00",
    "labels": {
        "en_US": "Category A",
        "fr_FR": "Catégorie A"
    }
}
JSON;
        $categories['categoryA1'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/categories/categoryA1"
        }
    },
    "code": "categoryA1",
    "parent": "categoryA",
    "updated" : "2016-06-14T13:12:50+02:00",
    "labels": {}
}
JSON;
        $categories['categoryA2'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/categories/categoryA2"
        }
    },
    "code": "categoryA2",
    "parent": "categoryA",
    "updated" : "2016-06-14T13:12:50+02:00",
    "labels": {}
}
JSON;
        $categories['categoryB'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/categories/categoryB"
        }
    },
    "code": "categoryB",
    "parent": "master",
    "updated" : "2016-06-14T13:12:50+02:00",
    "labels": {}
}
JSON;
        $categories['categoryC'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/categories/categoryC"
        }
    },
    "code": "categoryC",
    "parent": "master",
    "updated" : "2016-06-14T13:12:50+02:00",
    "labels": {}
}
JSON;
        $categories['master_china'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/categories/master_china"
        }
    },
    "code": "master_china",
    "parent": null,
    "updated" : "2016-06-14T13:12:50+02:00",
    "labels": {}
}
JSON;

        return $categories;
    }

    public function getStandardizedCategorieswithPositionInformation(): array
    {
        $categories['master'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/categories/master"
        }
    },
    "code": "master",
    "parent": null,
    "updated" : "2016-06-14T13:12:50+02:00",
    "position" : 1,
    "labels": {}
}
JSON;
        $categories['categoryA'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/categories/categoryA"
        }
    },
    "code": "categoryA",
    "parent": "master",
    "updated" : "2016-06-14T13:12:50+02:00",
    "position" : 1,
    "labels": {
        "en_US": "Category A",
        "fr_FR": "Catégorie A"
    }
}
JSON;
        $categories['categoryA1'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/categories/categoryA1"
        }
    },
    "code": "categoryA1",
    "parent": "categoryA",
    "updated" : "2016-06-14T13:12:50+02:00",
    "position" : 1,
    "labels": {}
}
JSON;
        $categories['categoryA2'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/categories/categoryA2"
        }
    },
    "code": "categoryA2",
    "parent": "categoryA",
    "updated" : "2016-06-14T13:12:50+02:00",
    "position" : 2,
    "labels": {}
}
JSON;
        $categories['categoryB'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/categories/categoryB"
        }
    },
    "code": "categoryB",
    "parent": "master",
    "updated" : "2016-06-14T13:12:50+02:00",
    "position" : 2,
    "labels": {}
}
JSON;
        $categories['categoryC'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/categories/categoryC"
        }
    },
    "code": "categoryC",
    "parent": "master",
    "updated" : "2016-06-14T13:12:50+02:00",
    "position" : 3,
    "labels": {}
}
JSON;
        $categories['master_china'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/categories/master_china"
        }
    },
    "code": "master_china",
    "parent": null,
    "updated" : "2016-06-14T13:12:50+02:00",
    "position" : 1,
    "labels": {}
}
JSON;

        return $categories;
    }

    private function assertSameResponse(string $expectedJson, Response $actualResponse) {
        $this->assertSame(Response::HTTP_OK, $actualResponse->getStatusCode());

        $responseContent = json_decode($actualResponse->getContent(), true);
        $expectedContent = json_decode($expectedJson, true);

        $this->normalizeCategories($responseContent['_embedded']['items']);
        $this->normalizeCategories($expectedContent['_embedded']['items']);

        $this->assertEquals($expectedContent, $responseContent);
    }

    private function normalizeCategories(array &$categories) {
        foreach ($categories as &$category) {
            NormalizedCategoryCleaner::clean($category);
        }
    }
}
