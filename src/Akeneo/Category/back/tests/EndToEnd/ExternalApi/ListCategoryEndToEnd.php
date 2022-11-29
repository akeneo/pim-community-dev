<?php

namespace Akeneo\Test\Category\EndToEnd\ExternalApi;

use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface;
use Akeneo\Test\Integration\Configuration;
use AkeneoTest\Pim\Enrichment\Integration\Normalizer\NormalizedCategoryCleaner;
use Symfony\Component\HttpFoundation\Response;

class ListCategoryEndToEnd extends ApiCategoryTestCase
{
    /**
     * @group critical
     */
    public function testListAllPaginatedCategories(): void
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
     * @param array<string, mixed> $data
     */
    protected function createCategory(array $data = []): CategoryInterface
    {
        $category = $this->get('pim_catalog.factory.category')->create();
        $this->get('pim_catalog.updater.category')->update($category, $data);
        $this->get('validator')->validate($category);
        $this->get('pim_catalog.saver.category')->save($category);

        return $category;
    }

    public function testListCategoriesByParent(): void
    {
        $this->createCategory(['parent' => 'categoryA1', 'code' => 'categoryA1-1']);
        $this->createCategory(['parent' => 'categoryA1-1', 'code' => 'categoryA1-1-1']);

        $categories = $this->getStandardizedCategories();
        $search = '{"parent":[{"operator":"=","value":"categoryA"}]}';
        $searchEncoded = $this->encodeStringWithSymfonyUrlGeneratorCompatibility($search);

        $client = $this->createAuthenticatedClient();
        $client->request('GET', 'api/rest/v1/categories?with_count=true&search='.$search);

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

    public function testListCategoriesWithCount(): void
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

    public function testListCategoriesByCodes(): void
    {
        $categories = $this->getStandardizedCategories();
        $search = '{"code":[{"operator":"IN","value":["master","categoryA2","master_china"]}]}';
        $searchEncoded = $this->encodeStringWithSymfonyUrlGeneratorCompatibility($search);

        $client = $this->createAuthenticatedClient();
        $client->request('GET', 'api/rest/v1/categories?limit=5&page=1&with_count=true&search='.$search);

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

    public function testOutOfRangeListCategories(): void
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

    public function testListCategoriesWithPosition(): void
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

    public function testListCategoriesWithValues(): void
    {
        $this->activateEnrichedFeatureFlag();
        $this->updateCategoryWithValues('master');
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/categories?search={"code":[{"operator":"IN","value":["master"]}]}&with_enriched_attributes=true');

        $categories = $this->getStandardizedCategorieswithAttributesValues();
        $expected = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/categories?page=1&limit=10&with_count=false&search=%7B%22code%22:%5B%7B%22operator%22:%22IN%22,%22value%22:%5B%22master%22%5D%7D%5D%7D&with_enriched_attributes=true"
        },
        "first": {
            "href": "http://localhost/api/rest/v1/categories?page=1&limit=10&with_count=false&search=%7B%22code%22:%5B%7B%22operator%22:%22IN%22,%22value%22:%5B%22master%22%5D%7D%5D%7D&with_enriched_attributes=true"
        }
    },
    "current_page": 1,
    "_embedded": {
        "items": [
            {$categories['master']}
        ]
    }
}
JSON;

        $this->assertSameResponse($expected, $client->getResponse());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    /**
     * @return array<string,mixed>
     */
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

    /**
     * @return array<string, mixed>
     */
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

    /**
     * @return array<string, mixed>
     */
    public function getStandardizedCategorieswithAttributesValues(): array
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
    "labels": {},
    "values": {$this->getStandardizedAttributesValues()}
}
JSON;

        return $categories;
    }

    public function getStandardizedAttributesValues(): string
    {
        $attributes = <<<JSON
{
    "attribute_codes": [
        "title|87939c45-1d85-4134-9579-d594fff65030",
        "photo|8587cda6-58c8-47fa-9278-033e1d8c735c"
    ],
    "photo|8587cda6-58c8-47fa-9278-033e1d8c735c": {
        "data": {
            "size": 168107,
            "extension": "jpg",
            "file_path": "8/8/3/d/883d041fc9f22ce42fee07d96c05b0b7ec7e66de_shoes.jpg",
            "mime_type": "image/jpeg",
            "original_filename": "shoes.jpg"
        },
        "type": "image",
        "locale": null,
        "channel": null,
        "attribute_code": "photo|8587cda6-58c8-47fa-9278-033e1d8c735c"
    },
    "title|87939c45-1d85-4134-9579-d594fff65030|ecommerce|en_US": {
        "data": "All the shoes you need!",
        "type": "text",
        "locale": "en_US",
        "channel": "ecommerce",
        "attribute_code": "title|87939c45-1d85-4134-9579-d594fff65030"
    },
    "title|87939c45-1d85-4134-9579-d594fff65030|ecommerce|fr_FR": {
        "data": "Les chaussures dont vous avez besoin !",
        "type": "text",
        "locale": "fr_FR",
        "channel": "ecommerce",
        "attribute_code": "title|87939c45-1d85-4134-9579-d594fff65030"
    }
}
JSON;
        return $attributes;
    }

    private function assertSameResponse(string $expectedJson, Response $actualResponse): void
    {
        $this->assertSame(Response::HTTP_OK, $actualResponse->getStatusCode());

        $responseContent = json_decode($actualResponse->getContent(), true);
        $expectedContent = json_decode($expectedJson, true);

        $this->normalizeCategories($responseContent['_embedded']['items']);
        $this->normalizeCategories($expectedContent['_embedded']['items']);

        $this->assertEquals($expectedContent, $responseContent);
    }

    /**
     * @param array<string, mixed> $categories
     */
    private function normalizeCategories(array &$categories): void
    {
        foreach ($categories as &$category) {
            NormalizedCategoryCleaner::clean($category);
        }
    }
}
