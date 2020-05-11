<?php

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\ProductModel\ExternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;

class ListUpdatedOrCreatedSinceNDaysProductModelEndToEnd extends AbstractProductModelTestCase
{
    /**
     * @group ce
     */
    public function testListUpdatedSinceNDays()
    {
        $search = sprintf('{"updated":[{"operator":"%s","value":2}]}', Operators::SINCE_LAST_N_DAYS);
        $this->assertOperatorSinceNDays($search);
    }

    /**
     * @group ce
     */
    public function testListCreatedSinceNDays()
    {
        $search = sprintf('{"created":[{"operator":"%s","value":2}]}', Operators::SINCE_LAST_N_DAYS);
        $this->assertOperatorSinceNDays($search);
    }

    private function assertOperatorSinceNDays(string $search): void
    {
        $standardizedProducts = $this->getStandardizedProductModels();
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/product-models?search=' . $search);
        $searchEncoded = rawurlencode($search);
        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href": "http://localhost/api/rest/v1/product-models?page=1&with_count=false&pagination_type=page&limit=10&search=$searchEncoded"},
        "first" : {"href": "http://localhost/api/rest/v1/product-models?page=1&with_count=false&pagination_type=page&limit=10&search=$searchEncoded"}
    },
    "current_page" : 1,
    "_embedded" : {
        "items" : [
            {$standardizedProducts['sweat']},
            {$standardizedProducts['shoes']},
            {$standardizedProducts['tshirt']},
            {$standardizedProducts['trousers']},
            {$standardizedProducts['hat']},
            {$standardizedProducts['handbag']}
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }
}
