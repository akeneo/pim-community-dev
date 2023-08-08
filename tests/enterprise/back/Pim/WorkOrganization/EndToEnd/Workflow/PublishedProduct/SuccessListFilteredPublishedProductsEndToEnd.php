<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\WorkOrganization\EndToEnd\Workflow\PublishedProduct;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetImageValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMeasurementValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Doctrine\Common\Collections\Collection;

class SuccessListFilteredPublishedProductsEndToEnd extends AbstractPublishedProductTestCase
{
    /** @var Collection */
    private $products;

    public function testFilterOnIdentifier(): void
    {
        $standardizedPublishedProducts = $this->getStandardizedPublishedProducts();
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/published-products?search={"identifier":[{"operator":"IN","value":["simple"]}]}');

        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href": "http://localhost/api/rest/v1/published-products?page=1&with_count=false&pagination_type=page&limit=10&search=%7B%22identifier%22:%5B%7B%22operator%22:%22IN%22,%22value%22:%5B%22simple%22%5D%7D%5D%7D"},
        "first" : {"href": "http://localhost/api/rest/v1/published-products?page=1&with_count=false&pagination_type=page&limit=10&search=%7B%22identifier%22:%5B%7B%22operator%22:%22IN%22,%22value%22:%5B%22simple%22%5D%7D%5D%7D"}
    },
    "current_page" : 1,
    "_embedded"    : {
		"items": [
            {$standardizedPublishedProducts['simple']}
		]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        // no locale, no scope, 1 category
        $product1 = $this->createProduct('simple', [
            new SetCategories(['master']),
            new SetMeasurementValue('a_metric', null, null, 10, 'KILOWATT'),
            new SetTextValue('a_text', null, null, 'Text'),
        ]);

        // localizable, categorized in 1 tree (master)
        $product2 = $this->createProduct('localizable', [
            new SetCategories(['categoryB']),
            new SetImageValue('a_localizable_image', null, 'en_US', $this->getFileInfoKey($this->getFixturePath('akeneo.jpg'))),
            new SetImageValue('a_localizable_image', null, 'fr_FR', $this->getFileInfoKey($this->getFixturePath('akeneo.jpg'))),
            new SetImageValue('a_localizable_image', null, 'de_DE', $this->getFileInfoKey($this->getFixturePath('akeneo.jpg'))),
        ]);

        $this->publishProduct($product1);
        $this->publishProduct($product2);

        $this->products = $this->get('pim_catalog.repository.product')->findAll();
    }

    /**
     * @return array
     */
    protected function getStandardizedPublishedProducts(): array
    {
        $standardizedPublishedProducts['simple'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/published-products/simple"
        }
    },
    "identifier": "simple",
    "family": null,
    "groups": [],
    "categories": ["master"],
    "enabled": true,
    "values": {
        "a_text": [{
            "locale": null,
            "scope": null,
            "data": "Text"
        }],
        "a_metric": [{
            "locale": null,
            "scope": null,
            "data": {
                "amount": "10.0000",
                "unit": "KILOWATT"
            }
        }]
    },
    "created": "2017-03-11T10:39:38+01:00",
    "updated": "2017-03-11T10:39:38+01:00",
    "associations": {},
    "quantified_associations": []
}
JSON;

        $standardizedPublishedProducts['localizable'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/published-products/localizable"
        }
    },
    "identifier": "localizable",
    "family": null,
    "groups": [],
    "categories": ["categoryB"],
    "enabled": true,
    "values": {
        "a_localizable_image": [{
            "locale": "de_DE",
            "scope": null,
            "data": "8/8/c/2/88c252d871f39e9b7a8d02f7bdc8f810d5faca1a_akeneo.jpg",
            "_links": {
                "download": {
                    "href": "http://localhost/api/rest/v1/media-files/8/8/c/2/88c252d871f39e9b7a8d02f7bdc8f810d5faca1a_akeneo.jpg/download"
                }
            }
        }, {
            "locale": "en_US",
            "scope": null,
            "data": "7\/1\/e\/c\/71ec99d718e277bd6ec86023cbe2f02dd54218b4_akeneo.jpg",
            "_links": {
                "download": {
                    "href": "http://localhost/api/rest/v1/media-files/c/2/0/f/c20f1a4b3e6515d5676e89d52fb9e25fa1d29bd8_akeneo.jpg/download"
                }
            }
        }, {
            "locale": "fr_FR",
            "scope": null,
            "data": "6\/7\/8\/3\/6783035ea95aefa68c1c0732a3ceb197319367fa_akeneo.jpg",
            "_links": {
                "download": {
                    "href": "http:\/\/localhost\/api\/rest\/v1\/media-files\/6\/7\/8\/3\/6783035ea95aefa68c1c0732a3ceb197319367fa_akeneo.jpg\/download"
                }
            }
        }]
    },
    "created": "2017-03-11T10:39:38+01:00",
    "updated": "2017-03-11T10:39:38+01:00",
    "associations": {},
    "quantified_associations": []
}
JSON;

        return $standardizedPublishedProducts;
    }
}
