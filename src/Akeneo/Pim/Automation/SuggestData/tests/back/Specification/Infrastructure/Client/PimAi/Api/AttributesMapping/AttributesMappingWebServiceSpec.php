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
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class AttributesMappingWebServiceSpec extends ObjectBehavior
{
    public function let(UriGenerator $uriGenerator, Client $httpClient): void
    {
        $this->beConstructedWith($uriGenerator, $httpClient);
    }

    public function it_is_a_attributes_mapping_webservice(): void
    {
        $this->shouldHaveType(AttributesMappingWebService::class);
    }

    public function it_fetches_attributes_mapping(
        ResponseInterface $apiResponse,
        StreamInterface $stream,
        $uriGenerator,
        $httpClient
    ): void {
        $familyCode = 'router';
        $route = sprintf('/api/mapping/%s/attributes', $familyCode);
        $uriGenerator->generate($route)->willReturn('/my_route');

        $apiResponse->getBody()->willReturn($stream);
        $stream->getContents()->willReturn($this->getApiJsonReturn());

        $httpClient->request('GET', '/my_route')->willReturn($apiResponse);

        $attributesMapping = $this->fetchByFamily($familyCode);
        $attributesMapping->getIterator()->count()->shouldReturn(2);
    }

    public function it_updates_attributes_mapping($uriGenerator, $httpClient): void
    {
        $familyCode = 'router';
        $mapping = ['foo' => 'bar'];

        $route = sprintf('/api/mapping/%s/attributes', $familyCode);
        $uriGenerator->generate($route)->willReturn('/my_route');

        $httpClient->request('PUT', '/my_route', ['form_params' => $mapping]);
    }

    private function getApiJsonReturn(): string
    {
        $directory = realpath(
            sprintf('%s/../../../../../../../../Infrastructure/Client/PimAi/Api/resources', __DIR__)
        );
        $filepath = sprintf('%s/%s.json', $directory, 'attributes-mapping-family-router');
        if (!file_exists($filepath)) {
            throw new \InvalidArgumentException(sprintf('File "%s" not found', $filepath));
        }

        return file_get_contents($filepath);
    }
}
