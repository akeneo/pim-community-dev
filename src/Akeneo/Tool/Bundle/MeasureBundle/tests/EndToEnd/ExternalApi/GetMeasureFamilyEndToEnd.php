<?php

namespace Akeneo\Tool\Bundle\MeasureBundle\tests\EndToEnd\ExternalApi;

use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class GetMeasureFamilyEndToEnd extends ApiTestCase
{
    public function testGetAMeasureFamily()
    {
        $standardMeasureFamily = <<<JSON
{
  "code": "Area",
  "standard": "SQUARE_METER",
  "units": [
    {
      "code": "SQUARE_MILLIMETER",
      "convert": {
        "mul": "0.000001"
      },
      "symbol": "mm\u00b2"
    },
    {
      "code": "SQUARE_CENTIMETER",
      "convert": {
        "mul": "0.0001"
      },
      "symbol": "cm\u00b2"
    },
    {
      "code": "SQUARE_DECIMETER",
      "convert": {
        "mul": "0.01"
      },
      "symbol": "dm\u00b2"
    },
    {
      "code": "SQUARE_METER",
      "convert": {
        "mul": "1"
      },
      "symbol": "m\u00b2"
    },
    {
      "code": "CENTIARE",
      "convert": {
        "mul": "1"
      },
      "symbol": "ca"
    },
    {
      "code": "SQUARE_DEKAMETER",
      "convert": {
        "mul": "100"
      },
      "symbol": "dam\u00b2"
    },
    {
      "code": "ARE",
      "convert": {
        "mul": "100"
      },
      "symbol": "a"
    },
    {
      "code": "SQUARE_HECTOMETER",
      "convert": {
        "mul": "10000"
      },
      "symbol": "hm\u00b2"
    },
    {
      "code": "HECTARE",
      "convert": {
        "mul": "10000"
      },
      "symbol": "ha"
    },
    {
      "code": "SQUARE_KILOMETER",
      "convert": {
        "mul": "1000000"
      },
      "symbol": "km\u00b2"
    },
    {
      "code": "SQUARE_MIL",
      "convert": {
        "mul": "0.00000000064516"
      },
      "symbol": "sq mil"
    },
    {
      "code": "SQUARE_INCH",
      "convert": {
        "mul": "0.00064516"
      },
      "symbol": "in\u00b2"
    },
    {
      "code": "SQUARE_FOOT",
      "convert": {
        "mul": "0.09290304"
      },
      "symbol": "ft\u00b2"
    },
    {
      "code": "SQUARE_YARD",
      "convert": {
        "mul": "0.83612736"
      },
      "symbol": "yd\u00b2"
    },
    {
      "code": "ARPENT",
      "convert": {
        "mul": "3418.89"
      },
      "symbol": "arpent"
    },
    {
      "code": "ACRE",
      "convert": {
        "mul": "4046.856422"
      },
      "symbol": "A"
    },
    {
      "code": "SQUARE_FURLONG",
      "convert": {
        "mul": "40468.726"
      },
      "symbol": "fur\u00b2"
    },
    {
      "code": "SQUARE_MILE",
      "convert": {
        "mul": "2589988.110336"
      },
      "symbol": "mi\u00b2"
    }
  ]
}
JSON;

        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/measure-families/Area');
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($standardMeasureFamily, $response->getContent());
    }

    public function testNotFoundAMeasureFamily()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/measure-families/not_found');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $expected = '{"code":404,"message":"Measure family with code \"not_found\" does not exist."}';
        $this->assertSame($expected, $response->getContent());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
