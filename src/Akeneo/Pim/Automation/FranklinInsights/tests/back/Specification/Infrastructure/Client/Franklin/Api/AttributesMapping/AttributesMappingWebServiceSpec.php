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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\AttributesMapping;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\AttributesMapping\AttributesMappingWebService;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\AuthenticatedApiInterface;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\FakeClient;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\GuzzleClient;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\UriGenerator;
use PhpSpec\ObjectBehavior;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class AttributesMappingWebServiceSpec extends ObjectBehavior
{
    public function let(UriGenerator $uriGenerator, GuzzleClient $httpClient, LoggerInterface $logger): void
    {
        $this->beConstructedWith($uriGenerator, $httpClient, $logger);
    }

    public function it_is_a_attributes_mapping_webservice(): void
    {
        $this->shouldHaveType(AttributesMappingWebService::class);
    }

    public function it_is_an_authenticated_webservice(): void
    {
        $this->shouldImplement(AuthenticatedApiInterface::class);
    }

    public function it_fetches_attributes_mapping(
        ResponseInterface $apiResponse,
        StreamInterface $stream,
        $uriGenerator,
        $httpClient
    ): void {
        $familyCode = new FamilyCode('router');
        $route = sprintf('/api/mapping/%s/attributes', $familyCode);
        $uriGenerator->generate($route)->willReturn('/my_route');

        $apiResponse->getBody()->willReturn($stream);
        $stream->getContents()->willReturn($this->getApiJsonReturn());

        $httpClient->request('GET', '/my_route')->willReturn($apiResponse);

        $attributesMapping = $this->fetchByFamily((string) $familyCode);
        $attributesMapping->getIterator()->count()->shouldReturn(4);
    }

    public function it_saves_attributes_mapping($uriGenerator, $httpClient): void
    {
        $familyCode = 'router';
        $mapping = ['foo' => 'bar'];

        $route = sprintf('/api/mapping/%s/attributes', $familyCode);
        $uriGenerator->generate($route)->willReturn('/my_route');

        $httpClient->request('PUT', '/my_route', ['form_params' => $mapping]);
    }

    public function it_logs_attributes_in_error(
        ResponseInterface $apiResponse,
        StreamInterface $stream,
        $uriGenerator,
        $httpClient,
        $logger
    )
    {
        $familyCode = new FamilyCode('router');
        $route = sprintf('/api/mapping/%s/attributes', $familyCode);
        $uriGenerator->generate($route)->willReturn('/my_route');

        $apiResponse->getBody()->willReturn($stream);
        $stream->getContents()->willReturn($this->getApiJsonReturn());

        $httpClient->request('GET', '/my_route')->willReturn($apiResponse);

        $attributesMapping = $this->fetchByFamily((string) $familyCode);

        $logger->error(
            'Unable to hydrate following AttributeMapping object',
            [
                'attribute' => ["from" => ["id" => "malformedAttribute", "label" => ["en_us" => "malformedAttribute"], "type" => "text"], "summary" => [], "status" => "pending"],
                'error_message' => 'Missing key "to" in attribute'
            ]
        )->shouldBeCalled();

        $attributesMapping->getIterator()->count()->shouldReturn(4);
    }

    private function getApiJsonReturn(): string
    {
        $filepath = realpath(FakeClient::FAKE_PATH) . '/mapping/router/attributes.json';
        if (!file_exists($filepath)) {
            throw new \InvalidArgumentException(sprintf('File "%s" not found', $filepath));
        }

        return file_get_contents($filepath);
    }
}
