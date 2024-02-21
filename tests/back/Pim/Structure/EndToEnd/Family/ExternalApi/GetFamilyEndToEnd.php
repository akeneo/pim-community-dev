<?php

namespace AkeneoTest\Pim\Structure\EndToEnd\Family\ExternalApi;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class GetFamilyEndToEnd extends ApiTestCase
{
    public function testGetAFamily()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/families/familyA');
        $expected = <<<JSON
{
    "code": "familyA",
    "attributes" : [
        "a_date",
        "a_file",
        "a_localizable_image",
        "a_localized_and_scopable_text_area",
        "a_metric",
        "a_multi_select",
        "a_number_float",
        "a_number_float_negative",
        "a_number_integer",
        "a_price",
        "a_ref_data_multi_select",
        "a_ref_data_simple_select",
        "a_scopable_price",
        "a_simple_select",
        "a_text",
        "a_text_area",
        "a_yes_no",
        "an_image",
        "sku"
    ],
    "attribute_as_label": "sku",
    "attribute_as_image": "an_image",
    "attribute_requirements": {
        "ecommerce" : [
            "a_date",
            "a_file",
            "a_localizable_image",
            "a_localized_and_scopable_text_area",
            "a_metric",
            "a_multi_select",
            "a_number_float",
            "a_number_float_negative",
            "a_number_integer",
            "a_price",
            "a_ref_data_multi_select",
            "a_ref_data_simple_select",
            "a_scopable_price",
            "a_simple_select",
            "a_text",
            "a_text_area",
            "a_yes_no",
            "an_image",
            "sku"
        ],
        "ecommerce_china" : ["sku"],
        "tablet" : [
            "a_date",
            "a_file",
            "a_localizable_image",
            "a_localized_and_scopable_text_area",
            "a_metric",
            "a_multi_select",
            "a_number_float",
            "a_number_float_negative",
            "a_number_integer",
            "a_price",
            "a_ref_data_multi_select",
            "a_ref_data_simple_select",
            "a_scopable_price",
            "a_simple_select",
            "a_text",
            "a_text_area",
            "a_yes_no",
            "an_image",
            "sku"
        ]
    },
    "labels": {
        "fr_FR" : "Une famille A",
        "en_US" : "A family A"
    }
}
JSON;
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testNotFoundFamily()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/families/not_found');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertCount(2, $content, 'response contains 2 items');
        $this->assertSame(Response::HTTP_NOT_FOUND, $content['code']);
        $this->assertSame('Family "not_found" does not exist.', $content['message']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
