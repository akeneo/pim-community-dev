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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Api\OptionsMapping;

use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Api\AuthenticatedApiInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Api\OptionsMapping\OptionsMappingInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Api\OptionsMapping\OptionsMappingWebService;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Exception\BadRequestException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Exception\FranklinServerException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\GuzzleClient;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\UriGenerator;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\ValueObject\OptionsMapping;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use PhpSpec\ObjectBehavior;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class OptionsMappingWebServiceSpec extends ObjectBehavior
{
    public function let(
        UriGenerator $uriGenerator,
        GuzzleClient $httpClient,
        ResponseInterface $response,
        StreamInterface $stream
    ): void {
        $response->getBody()->willReturn($stream);
        $this->beConstructedWith($uriGenerator, $httpClient);
    }

    public function it_is_an_attribute_options_mapping_web_service(): void
    {
        $this->shouldHaveType(OptionsMappingWebService::class);
    }

    public function it_is_an_authenticated_webservice(): void
    {
        $this->shouldImplement(AuthenticatedApiInterface::class);
    }

    public function it_implements_attribute_options_mapping_interface(): void
    {
        $this->shouldImplement(OptionsMappingInterface::class);
    }

    public function it_fetches_attribute_options_mapping($uriGenerator, $httpClient, $response, $stream): void
    {
        $fakeData = [
            'mapping' => [
                [
                    'from' => ['id' => 'color_1', 'label' => ['en_US' => 'Color 1']],
                    'to' => null,
                    'status' => 'pending',
                ],
                [
                    'from' => ['id' => 'color_2', 'label' => ['en_US' => 'Color 2']],
                    'to' => ['id' => 'pim_color'],
                    'status' => 'active',
                ],
            ],
        ];
        $uriGenerator->generate('/api/mapping/foo/attributes/bar/options')->willReturn('foo');
        $httpClient->request('GET', 'foo')->willReturn($response);
        $stream->getContents()->willReturn(json_encode($fakeData));

        $this
            ->fetchByFamilyAndAttribute('foo', 'bar')
            ->shouldReturnAnInstanceOf(OptionsMapping::class);
    }

    public function it_throws_a_server_exception_when_an_empty_response_is_sent_from_franklin(
        $uriGenerator,
        $httpClient,
        $response,
        $stream
    ): void {
        $response->getBody()->willReturn($stream);
        $stream->getContents()->willReturn('');

        $uriGenerator->generate('/api/mapping/foo/attributes/bar/options')->willReturn('foo');
        $httpClient->request('GET', 'foo')->willReturn($response);

        $this
            ->shouldThrow(FranklinServerException::class)
            ->during('fetchByFamilyAndAttribute', ['foo', 'bar']);
    }

    public function it_throws_a_server_exception_when_there_is_no_mapping_key(
        $uriGenerator,
        $httpClient,
        $response,
        $stream
    ): void {
        $response->getBody()->willReturn($stream);
        $stream->getContents()->willReturn(json_encode([]));

        $uriGenerator->generate('/api/mapping/foo/attributes/bar/options')->willReturn('foo');
        $httpClient->request('GET', 'foo')->willReturn($response);

        $this
            ->shouldThrow(FranklinServerException::class)
            ->during('fetchByFamilyAndAttribute', ['foo', 'bar']);
    }

    public function it_throws_a_server_exception_when_franklin_sent_a_server_exception(
        $uriGenerator,
        $httpClient,
        $response,
        $stream,
        RequestInterface $request
    ): void {
        $fakeData = [
            [
                'from' => ['id' => 'color_1', 'label' => ['en_US' => 'Color 1']],
                'to' => null,
                'status' => 'pending',
            ],
        ];
        $response->getBody()->willReturn($stream);
        $stream->getContents()->willReturn($fakeData);

        $uriGenerator->generate('/api/mapping/foo/attributes/bar/options')->willReturn('foo');
        $httpClient
            ->request('GET', 'foo')
            ->willThrow(new ServerException('foo', $request->getWrappedObject()));

        $this->shouldThrow(FranklinServerException::class)->during('fetchByFamilyAndAttribute', ['foo', 'bar']);
    }

    public function it_throws_a_bad_request_exception_when_franklin_sent_a_client_exception(
        $uriGenerator,
        $httpClient,
        $response,
        $stream,
        RequestInterface $request
    ): void {
        $fakeData = [
            [
                'from' => ['id' => 'color_1', 'label' => ['en_US' => 'Color 1']],
                'to' => null,
                'status' => 'pending',
            ],
        ];
        $response->getBody()->willReturn($stream);
        $stream->getContents()->willReturn($fakeData);

        $uriGenerator->generate('/api/mapping/foo/attributes/bar/options')->willReturn('foo');
        $httpClient
            ->request('GET', 'foo')
            ->willThrow(new ClientException('foo', $request->getWrappedObject()));

        $this->shouldThrow(BadRequestException::class)->during('fetchByFamilyAndAttribute', ['foo', 'bar']);
    }
}
