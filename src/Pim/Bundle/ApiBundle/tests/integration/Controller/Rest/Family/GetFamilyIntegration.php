<?php

namespace Pim\Bundle\ApiBundle\tests\integration\Controller\Rest\Family;

use Akeneo\Test\Integration\Configuration;
use Pim\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class GetFamilyIntegration extends ApiTestCase
{
    public function testGetAFamily()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/families/familyA');
        $standardFamily = [
            'code'       => 'familyA',
            'attributes' => [
                0  => 'a_date',
                1  => 'a_file',
                2  => 'a_localizable_image',
                3  => 'a_localized_and_scopable_text_area',
                4  => 'a_metric',
                5  => 'a_multi_select',
                6  => 'a_number_float',
                7  => 'a_number_float_negative',
                8  => 'a_number_integer',
                9  => 'a_price',
                10 => 'a_ref_data_multi_select',
                11 => 'a_ref_data_simple_select',
                12 => 'a_scopable_price',
                13 => 'a_simple_select',
                14 => 'a_text',
                15 => 'a_text_area',
                16 => 'a_yes_no',
                17 => 'an_image',
                18 => 'sku',
            ],
            'attribute_as_label'     => 'sku',
            'attribute_requirements' => [
                'ecommerce' => [
                    0  => 'a_date',
                    1  => 'a_file',
                    2  => 'a_localizable_image',
                    3  => 'a_localized_and_scopable_text_area',
                    4  => 'a_metric',
                    5  => 'a_multi_select',
                    6  => 'a_number_float',
                    7  => 'a_number_float_negative',
                    8  => 'a_number_integer',
                    9  => 'a_price',
                    10 => 'a_ref_data_multi_select',
                    11 => 'a_ref_data_simple_select',
                    12 => 'a_scopable_price',
                    13 => 'a_simple_select',
                    14 => 'a_text',
                    15 => 'a_text_area',
                    16 => 'a_yes_no',
                    17 => 'an_image',
                    18 => 'sku',
                ],
                'ecommerce_china' => [
                    0 => 'sku'
                ],
                'tablet' => [
                    0  => 'a_date',
                    1  => 'a_file',
                    2  => 'a_localizable_image',
                    3  => 'a_localized_and_scopable_text_area',
                    4  => 'a_metric',
                    5  => 'a_multi_select',
                    6  => 'a_number_float',
                    7  => 'a_number_float_negative',
                    8  => 'a_number_integer',
                    9  => 'a_price',
                    10 => 'a_ref_data_multi_select',
                    11 => 'a_ref_data_simple_select',
                    12 => 'a_scopable_price',
                    13 => 'a_simple_select',
                    14 => 'a_text',
                    15 => 'a_text_area',
                    16 => 'a_yes_no',
                    17 => 'an_image',
                    18 => 'sku',
                ],
            ],
            'labels'     => [],
        ];

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame($standardFamily, json_decode($response->getContent(), true));
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
        return new Configuration(
            [Configuration::getTechnicalCatalogPath()],
            false
        );
    }
}
