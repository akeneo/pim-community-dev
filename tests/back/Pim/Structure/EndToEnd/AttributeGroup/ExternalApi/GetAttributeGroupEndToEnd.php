<?php

namespace AkeneoTest\Pim\Structure\EndToEnd\AttributeGroup\ExternalApi;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class GetAttributeGroupEndToEnd extends ApiTestCase
{
    public function testGetAnAttributeGroup()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/attribute-groups/attributeGroupA');

        $standardAttributeGroup = <<<JSON
{
    "code": "attributeGroupA",
    "sort_order": 1,
    "attributes": [
        "sku",
        "a_date",
        "a_file",
        "an_image",
        "a_price",
        "a_price_without_decimal",
        "a_ref_data_multi_select",
        "a_ref_data_simple_select",
        "a_text",
        "a_regexp",
        "a_text_area",
        "a_yes_no",
        "a_scopable_price",
        "a_localized_and_scopable_text_area"

    ],
    "labels": {
        "en_US": "Attribute group A",
        "fr_FR": "Groupe d'attribut A"
    }
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($standardAttributeGroup, $response->getContent());
    }

    public function testNotFoundAnAttributeGroup()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/attribute-groups/not_found');

        $response = $client->getResponse();

        $expected = <<<JSON
{
	"code": 404,
	"message": "Attribute group \"not_found\" does not exist."
}
JSON;

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
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
