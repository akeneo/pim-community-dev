<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\AttributesMapping;

use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\AttributesMapping\AttributesMappingWebService;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Client;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\UriGenerator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class AttributesMappingWebServiceSpec  extends ObjectBehavior
{
    public function let(UriGenerator $uriGenerator, Client $httpClient)
    {
        $this->beConstructedWith($uriGenerator, $httpClient);
    }

    public function it_is_a_attributes_mapping_webservice()
    {
        $this->shouldHaveType(AttributesMappingWebService::class);
    }

    public function it_fetches_attributes_mapping(
        ResponseInterface $apiResponse,
        StreamInterface $stream,
        $uriGenerator,
        $httpClient
    ) {
        $uriGenerator->generate(Argument::any())->willReturn('my/route');

        $apiResponse->getBody()->willReturn($stream);
        $stream->getContents()->willReturn($this->getApiJsonReturn());

        $httpClient->request('GET', 'my/route')->willReturn($apiResponse);

        $attributesMapping = $this->fetchByFamily('router');
        $attributesMapping->getIterator()->count()->shouldReturn(2);
    }

    private function getApiJsonReturn(): string
    {
        return
<<<JSON
[
  {
    "from": {
      "id": "product_weight",
      "label": {
        "en_us": "Product Weight"
      }
    },
    "to": null,
    "type": "metric",
    "status": "pending"
  },
  {
    "from": {
      "id": "color",
      "label": {
        "en_us": "Color"
      }
    },
    "to": null,
    "type": "multiselect",
    "status": "active"
  }
]
JSON;
    }
}
