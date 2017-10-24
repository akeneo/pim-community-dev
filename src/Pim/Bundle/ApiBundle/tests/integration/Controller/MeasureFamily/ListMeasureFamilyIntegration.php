<?php

namespace Pim\Bundle\ApiBundle\tests\integration\Controller\MeasureFamily;

use Akeneo\Test\Integration\Configuration;
use Pim\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class ListMeasureFamilyIntegration extends ApiTestCase
{
    public function testListMeasureFamily()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/measure-families');
        $measureFamilies = $this->getStandardizedMeasureFamilies();

        $expected = <<<JSON
{
  "_links": {
    "self": {
      "href": "http:\/\/localhost\/api\/rest\/v1\/measure-families?page=1&limit=10&with_count=false"
    },
    "first": {
      "href": "http:\/\/localhost\/api\/rest\/v1\/measure-families?page=1&limit=10&with_count=false"
    },
    "next": {
      "href": "http:\/\/localhost\/api\/rest\/v1\/measure-families?page=2&limit=10&with_count=false"
    }
  },
  "current_page": 1,
  "_embedded": {
    "items": [
      {$measureFamilies['area']},
      {$measureFamilies['binary']},
      {$measureFamilies['decibel']},
      {$measureFamilies['frequency']},
      {$measureFamilies['length']},
      {$measureFamilies['power']},
      {$measureFamilies['voltage']},
      {$measureFamilies['intensity']},
      {$measureFamilies['resistance']},
      {$measureFamilies['speed']}
    ]
  }
}
JSON;

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testOutOfRangeListMeasureFamily()
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', 'api/rest/v1/measure-families?page=3');

        $expected = <<<JSON
{
  "_links": {
    "self": {
      "href": "http:\/\/localhost\/api\/rest\/v1\/measure-families?page=3&limit=10&with_count=false"
    },
    "first": {
      "href": "http:\/\/localhost\/api\/rest\/v1\/measure-families?page=1&limit=10&with_count=false"
    },
    "previous": {
      "href": "http:\/\/localhost\/api\/rest\/v1\/measure-families?page=2&limit=10&with_count=false"
    }
  },
  "current_page": 3,
  "_embedded": {
    "items": []
  }
}
JSON;

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testPaginationListMeasureFamily()
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', 'api/rest/v1/measure-families?page=2&limit=3');
        $measureFamilies = $this->getStandardizedMeasureFamilies();

        $expected = <<<JSON
{
  "_links": {
    "self": {
      "href": "http:\/\/localhost\/api\/rest\/v1\/measure-families?page=2&limit=3&with_count=false"
    },
    "first": {
      "href": "http:\/\/localhost\/api\/rest\/v1\/measure-families?page=1&limit=3&with_count=false"
    },
    "next": {
      "href": "http:\/\/localhost\/api\/rest\/v1\/measure-families?page=3&limit=3&with_count=false"
    },
    "previous": {
      "href": "http:\/\/localhost\/api\/rest\/v1\/measure-families?page=1&limit=3&with_count=false"
    }
  },
  "current_page": 2,
  "_embedded": {
    "items": [
      {$measureFamilies['frequency']},
      {$measureFamilies['length']},
      {$measureFamilies['power']}
    ]
  }
}
JSON;

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testListOfMeasureFamiliesWithCount()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/measure-families?with_count=true&limit=1');
        $measureFamilies = $this->getStandardizedMeasureFamilies();

        $expected = <<<JSON
{
  "_links": {
    "self": {
      "href": "http:\/\/localhost\/api\/rest\/v1\/measure-families?page=1&limit=1&with_count=true"
    },
    "first": {
      "href": "http:\/\/localhost\/api\/rest\/v1\/measure-families?page=1&limit=1&with_count=true"
    },
    "next": {
      "href": "http:\/\/localhost\/api\/rest\/v1\/measure-families?page=2&limit=1&with_count=true"
    }
  },
  "current_page": 1,
  "items_count": 18,
  "_embedded": {
    "items": [
      {$measureFamilies['area']}
    ]
  }
}
JSON;

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testUnknownPaginationType()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/measure-families?pagination_type=search_after');

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $expected = '{"code":422,"message":"Pagination type is not supported."}';
        $this->assertEquals($response->getContent(), $expected);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function getStandardizedMeasureFamilies()
    {
        $measureFamilies['area'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http:\/\/localhost\/api\/rest\/v1\/measure-families\/area"
        }
    },
    "code": "area",
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

        $measureFamilies['binary'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http:\/\/localhost\/api\/rest\/v1\/measure-families\/binary"
      }
    },
    "code": "binary",
    "standard": "BYTE",
    "units": [
      {
          "code": "BIT",
        "convert": {
          "mul": "0.125"
        },
        "symbol": "b"
      },
      {
          "code": "BYTE",
        "convert": {
          "mul": "1"
        },
        "symbol": "B"
      },
      {
          "code": "KILOBYTE",
        "convert": {
          "mul": "1024"
        },
        "symbol": "kB"
      },
      {
          "code": "MEGABYTE",
        "convert": {
          "mul": "1048576"
        },
        "symbol": "MB"
      },
      {
          "code": "GIGABYTE",
        "convert": {
          "mul": "1073741824"
        },
        "symbol": "GB"
      },
      {
          "code": "TERABYTE",
        "convert": {
          "mul": "1099511627776"
        },
        "symbol": "TB"
      }
    ]
}
JSON;

        $measureFamilies['decibel'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http:\/\/localhost\/api\/rest\/v1\/measure-families\/decibel"
      }
    },
    "code": "decibel",
    "standard": "DECIBEL",
    "units": [
      {
          "code": "DECIBEL",
        "convert": {
          "mul": "1"
        },
        "symbol": "dB"
      }
    ]
}
JSON;

        $measureFamilies['frequency'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http:\/\/localhost\/api\/rest\/v1\/measure-families\/frequency"
      }
    },
    "code": "frequency",
    "standard": "HERTZ",
    "units": [
      {
          "code": "HERTZ",
        "convert": {
          "mul": "1"
        },
        "symbol": "Hz"
      },
      {
          "code": "KILOHERTZ",
        "convert": {
          "mul": "1000"
        },
        "symbol": "kHz"
      },
      {
          "code": "MEGAHERTZ",
        "convert": {
          "mul": "1000000"
        },
        "symbol": "MHz"
      },
      {
          "code": "GIGAHERTZ",
        "convert": {
          "mul": "1000000000"
        },
        "symbol": "GHz"
      },
      {
          "code": "TERAHERTZ",
        "convert": {
          "mul": "1000000000000"
        },
        "symbol": "THz"
      }
    ]
}
JSON;
        $measureFamilies['length'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http:\/\/localhost\/api\/rest\/v1\/measure-families\/length"
        }
    },
    "code": "length",
    "standard": "METER",
    "units": [
    {
      "code": "MILLIMETER",
    "convert": {
      "mul": "0.001"
    },
    "symbol": "mm"
    },
    {
      "code": "CENTIMETER",
    "convert": {
      "mul": "0.01"
    },
    "symbol": "cm"
    },
    {
      "code": "DECIMETER",
    "convert": {
      "mul": "0.1"
    },
    "symbol": "dm"
    },
    {
      "code": "METER",
    "convert": {
      "mul": "1"
    },
    "symbol": "m"
    },
    {
      "code": "DEKAMETER",
    "convert": {
      "mul": "10"
    },
    "symbol": "dam"
    },
    {
      "code": "HECTOMETER",
    "convert": {
      "mul": "100"
    },
    "symbol": "hm"
    },
    {
      "code": "KILOMETER",
    "convert": {
      "mul": "1000"
    },
    "symbol": "km"
    },
    {
      "code": "MIL",
    "convert": {
      "mul": "0.0000254"
    },
    "symbol": "mil"
    },
    {
      "code": "INCH",
    "convert": {
      "mul": "0.0254"
    },
    "symbol": "in"
    },
    {
      "code": "FEET",
    "convert": {
      "mul": "0.3048"
    },
    "symbol": "ft"
    },
    {
      "code": "YARD",
    "convert": {
      "mul": "0.9144"
    },
    "symbol": "yd"
    },
    {
      "code": "CHAIN",
    "convert": {
      "mul": "20.1168"
    },
    "symbol": "ch"
    },
    {
      "code": "FURLONG",
    "convert": {
      "mul": "201.168"
    },
    "symbol": "fur"
    },
    {
      "code": "MILE",
    "convert": {
      "mul": "1609.344"
    },
    "symbol": "mi"
    }
    ]
}
JSON;
        $measureFamilies['power'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http:\/\/localhost\/api\/rest\/v1\/measure-families\/power"
      }
    },
    "code": "power",
    "standard": "WATT",
    "units": [
      {
          "code": "WATT",
        "convert": {
          "mul": "1"
        },
        "symbol": "W"
      },
      {
          "code": "KILOWATT",
        "convert": {
          "mul": "1000"
        },
        "symbol": "kW"
      },
      {
          "code": "MEGAWATT",
        "convert": {
          "mul": "1000000"
        },
        "symbol": "MW"
      },
      {
          "code": "GIGAWATT",
        "convert": {
          "mul": "1000000000"
        },
        "symbol": "GW"
      },
      {
          "code": "TERAWATT",
        "convert": {
          "mul": "1000000000000"
        },
        "symbol": "TW"
      }
    ]
}
JSON;

        $measureFamilies['voltage'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http:\/\/localhost\/api\/rest\/v1\/measure-families\/voltage"
      }
    },
    "code": "voltage",
    "standard": "VOLT",
    "units": [
      {
          "code": "MILLIVOLT",
        "convert": {
          "mul": "0.001"
        },
        "symbol": "mV"
      },
      {
          "code": "CENTIVOLT",
        "convert": {
          "mul": "0.01"
        },
        "symbol": "cV"
      },
      {
          "code": "DECIVOLT",
        "convert": {
          "mul": "0.1"
        },
        "symbol": "dV"
      },
      {
          "code": "VOLT",
        "convert": {
          "mul": "1"
        },
        "symbol": "V"
      },
      {
          "code": "DEKAVOLT",
        "convert": {
          "mul": "10"
        },
        "symbol": "daV"
      },
      {
          "code": "HECTOVOLT",
        "convert": {
          "mul": "100"
        },
        "symbol": "hV"
      },
      {
          "code": "KILOVOLT",
        "convert": {
          "mul": "1000"
        },
        "symbol": "kV"
      }
    ]
}
JSON;

        $measureFamilies['intensity'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http:\/\/localhost\/api\/rest\/v1\/measure-families\/intensity"
      }
    },
    "code": "intensity",
    "standard": "AMPERE",
    "units": [
      {
          "code": "MILLIAMPERE",
        "convert": {
          "mul": "0.001"
        },
        "symbol": "mA"
      },
      {
          "code": "CENTIAMPERE",
        "convert": {
          "mul": "0.01"
        },
        "symbol": "cA"
      },
      {
          "code": "DECIAMPERE",
        "convert": {
          "mul": "0.1"
        },
        "symbol": "dA"
      },
      {
          "code": "AMPERE",
        "convert": {
          "mul": "1"
        },
        "symbol": "A"
      },
      {
          "code": "DEKAMPERE",
        "convert": {
          "mul": "10"
        },
        "symbol": "daA"
      },
      {
          "code": "HECTOAMPERE",
        "convert": {
          "mul": "100"
        },
        "symbol": "hA"
      },
      {
          "code": "KILOAMPERE",
        "convert": {
          "mul": "1000"
        },
        "symbol": "kA"
      }
    ]
}
JSON;

        $measureFamilies['resistance'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http:\/\/localhost\/api\/rest\/v1\/measure-families\/resistance"
      }
    },
    "code": "resistance",
    "standard": "OHM",
    "units": [
      {
          "code": "MILLIOHM",
        "convert": {
          "mul": "0.001"
        },
        "symbol": "m\u03a9"
      },
      {
          "code": "CENTIOHM",
        "convert": {
          "mul": "0.01"
        },
        "symbol": "c\u03a9"
      },
      {
          "code": "DECIOHM",
        "convert": {
          "mul": "0.1"
        },
        "symbol": "d\u03a9"
      },
      {
          "code": "OHM",
        "convert": {
          "mul": "1"
        },
        "symbol": "\u03a9"
      },
      {
          "code": "DEKAOHM",
        "convert": {
          "mul": "10"
        },
        "symbol": "da\u03a9"
      },
      {
          "code": "HECTOHM",
        "convert": {
          "mul": "100"
        },
        "symbol": "h\u03a9"
      },
      {
          "code": "KILOHM",
        "convert": {
          "mul": "1000"
        },
        "symbol": "k\u03a9"
      },
      {
          "code": "MEGOHM",
        "convert": {
          "mul": "1000000"
        },
        "symbol": "M\u03a9"
      }
    ]
}
JSON;

        $measureFamilies['speed'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http:\/\/localhost\/api\/rest\/v1\/measure-families\/speed"
      }
    },
    "code": "speed",
    "standard": "METER_PER_SECOND",
    "units": [
      {
          "code": "METER_PER_SECOND",
        "convert": {
          "mul": "1"
        },
        "symbol": "mdivs"
      },
      {
          "code": "METER_PER_MINUTE",
        "convert": {
          "div": "60"
        },
        "symbol": "mdivm"
      },
      {
          "code": "METER_PER_HOUR",
        "convert": {
          "mul": "1",
          "div": "3600"
        },
        "symbol": "mdivh"
      },
      {
          "code": "KILOMETER_PER_HOUR",
        "convert": {
          "mul": "1000",
          "div": "3600"
        },
        "symbol": "kmdivh"
      },
      {
          "code": "FOOT_PER_SECOND",
        "convert": {
          "mul": "0.3048"
        },
        "symbol": "ftdivs"
      },
      {
          "code": "FOOT_PER_HOUR",
        "convert": {
          "mul": "0.3048",
          "div": "3600"
        },
        "symbol": "ftdivh"
      },
      {
          "code": "YARD_PER_HOUR",
        "convert": {
          "mul": "0.9144",
          "div": "3600"
        },
        "symbol": "yddivh"
      },
      {
          "code": "MILE_PER_HOUR",
        "convert": {
          "mul": "1609.344",
          "div": "3600"
        },
        "symbol": "midivh"
      }
    ]
}
JSON;

        return $measureFamilies;
    }
}
