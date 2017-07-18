<?php

namespace PimEnterprise\Bundle\ApiBundle\tests\integration\Controller\Product;

use Pim\Component\Catalog\tests\integration\Normalizer\NormalizedProductCleaner;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

/**
 * +----------+-------------------------------+-----------------------------------+-----------------------------------------------------+
 * |          |          Categories           |             Locales               |                  Attribute groups                   |
 * +  Roles   +-------------------------------+-----------------------------------+-----------------------------------+-----------------+
 * |          |   categoryA2  |   categoryB   |   en_US   |   fr_FR   |   de_DE   | attributeGroupA | attributeGroupB | attributeGroupC |
 * +----------+-------------------------------+-----------------------------------+-----------------------------------------------------+
 * | Redactor |      View     |     -         | View,Edit |    View   |     -     |    View,Edit    |      View       |        -        |
 * | Manager  | View,Edit,Own | View,Edit,Own | View,Edit | View,Edit | View,Edit |    View,Edit    |    View,Edit    |    View,Edit    |
 * +----------+-------------------------------+-----------------------------------+-----------------------------------------------------+
 */
class GetProductWithPermissionsIntegration extends AbstractProductTestCase
{
    public function testProductViewableByManager()
    {
        $standardizedProducts = $this->getStandardizedProducts();
        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');

        $client->request('GET', 'api/rest/v1/products/product_viewable_by_everybody_1');

        $this->assertResponse($client->getResponse(), $standardizedProducts['product_viewable_by_everybody_1']);
    }

    public function testProductAttributeNotViewableByRedactor()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('GET', 'api/rest/v1/products?attributes=a_metric_without_decimal_negative');
        $this->assert($client, 'Attribute "a_metric_without_decimal_negative" does not exist.');
    }

    public function testProductAttributesNotViewableByRedactor()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('GET', 'api/rest/v1/products?attributes=a_metric_without_decimal_negative,a_localized_and_scopable_text_area');
        $this->assert($client, 'Attribute "a_metric_without_decimal_negative" does not exist.');
    }

    public function testProductOneAttributeNotViewableByRedactor()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('GET', 'api/rest/v1/products?locales=a_multi_select,a_metric_without_decimal_negative,a_localized_and_scopable_text_area');
        $this->assert($client, 'Attributes "a_multi_select, a_metric_without_decimal_negative" do not exist.');
    }

    public function testProductLocaleNotViewableByRedactor()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('GET', 'api/rest/v1/products?locales=de_DE');
        $this->assert($client, 'Locale "de_DE" does not exist.');
    }

    public function testProductLocalesNotViewableByRedactor()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('GET', 'api/rest/v1/products?locales=de_DE,en_US');
        $this->assert($client, 'Locales "de_DE, en_US" do not exist.');
    }

    public function testProductViewableByRedactor()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('GET', 'api/rest/v1/products/product_viewable_by_everybody_1');
        $expected = <<<JSON
{
    "identifier": "product_viewable_by_everybody_1",
    "family": null,
    "groups": [],
    "variant_group": null,
    "categories": ["categoryA2"],
    "enabled": true,
    "values": {
        "a_number_float": [
            {
                "data": "12.05",
                "locale": null,
                "scope": null
            }
        ],
        "a_localizable_image": [
            {
                "data": "3/3/6/a/336af1d213f9953530b3a7c4b4aeaf57615dbaaf_akeneo.jpg",
                "locale": "en_US",
                "scope": null,
                "_links": {
                    "download": {
                        "href": "http://localhost/api/rest/v1/media-files/3/3/6/a/336af1d213f9953530b3a7c4b4aeaf57615dbaaf_akeneo.jpg"
                    }
                }
            },
            {
                "data": "3/3/6/a/336af1d213f9953530b3a7c4b4aeaf57615dbaaf_akeneo.jpg",
                "locale": "fr_FR",
                "scope": null,
                "_links": {
                    "download": {
                        "href": "http://localhost/api/rest/v1/media-files/3/3/6/a/336af1d213f9953530b3a7c4b4aeaf57615dbaaf_akeneo.jpg"
                    }
                }
            }
        ],
        "a_localized_and_scopable_text_area": [
            { "data": "EN ecommerce", "locale": "en_US", "scope": "ecommerce" },
            { "data": "FR ecommerce", "locale": "fr_FR", "scope": "ecommerce" }
        ]
    },
    "created": "2017-03-11T10:39:38+01:00",
    "updated": "2017-03-11T10:39:38+01:00",
    "associations": {}
}
JSON;

        $this->assertResponse($client->getResponse(), $expected);
    }

    public function testNotAccessibleProductForRedactor()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('GET', 'api/rest/v1/products/product_not_viewable_by_redactor');
        $this->assertSame(403, $client->getResponse()->getStatusCode());
    }

    /**
     * @param Response $response
     * @param string   $expected
     */
    private function assertResponse(Response $response, $expected)
    {
        $result = json_decode($response->getContent(), true);
        $expected = json_decode($expected, true);
        unset($expected['_links']);

        NormalizedProductCleaner::clean($expected);
        NormalizedProductCleaner::clean($result);

        $this->assertEquals($expected, $result);
    }

    /**
     * @param Client $client
     * @param string $message
     */
    private function assert(Client $client, $message)
    {
        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertCount(2, $content);
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $content['code']);

        $this->assertSame($message, $content['message']);
    }
}
