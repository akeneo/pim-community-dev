<?php

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Category\ExternalApi;

use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
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
        $firstPageResponse = $firstPageClient->getResponse();
        $this->assertSame(Response::HTTP_OK, $firstPageResponse->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedFirstPage, $firstPageResponse->getContent());

        $secondPageResponse = $secondPageClient->getResponse();
        $this->assertSame(Response::HTTP_OK, $secondPageResponse->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedSecondPage, $secondPageResponse->getContent());
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
                "labels": {}
            },
            {$categories['categoryA2']}
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

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
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
    "labels": {}
}
JSON;

        return $categories;
    }
}
