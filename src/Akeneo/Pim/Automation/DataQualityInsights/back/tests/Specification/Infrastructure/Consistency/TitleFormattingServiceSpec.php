<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Consistency;

use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency\Text\TitleFormattingServiceInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Exception\UnableToProvideATitleSuggestion;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductTitle;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Consistency\TitleFormattingService;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Consistency\TitleFormattingToken;
use GuzzleHttp\ClientInterface;
use PhpSpec\ObjectBehavior;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

class TitleFormattingServiceSpec extends ObjectBehavior
{
    public function let(
        ClientInterface $client,
        LoggerInterface $logger,
        TitleFormattingToken $titleFormattingToken
    ) {
        $this->beConstructedWith($client, $logger, $titleFormattingToken);
    }

    public function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(TitleFormattingService::class);
        $this->shouldImplement(TitleFormattingServiceInterface::class);
    }

    public function it_logs_and_throws_exception_if_errors(
        $client,
        $logger,
        $titleFormattingToken,
        ResponseInterface $response,
        StreamInterface $body
    ) {
        $titleFormattingToken->getTokenAsString()->willReturn('a.jwt.token');

        $client->request('GET', 'api/data-quality-insights/title', [
                'query' => [
                    'title' => 'text'
                ],
                'headers' => [
                    'X-AKENEO-AUTH' => 'a.jwt.token'
                ]
            ]
        )->willReturn($response);

        $response->getBody()->willReturn($body);
        $body->getContents()->willReturn("{'suggestion': ''}");

        $response->getStatusCode()->willReturn(Response::HTTP_I_AM_A_TEAPOT);
        $logger->error('An error occurred while trying to provide a title suggestion.', [
            'http_response_status_code' => 418,
            'title' => 'text'
        ])->shouldBeCalled();

        $this->shouldThrow(UnableToProvideATitleSuggestion::class)
            ->during('format', [new ProductTitle('text')]);
    }

    public function it_logs_and_throws_exception_if_unauthorized(
        $client,
        $logger,
        $titleFormattingToken,
        ResponseInterface $response,
        StreamInterface $body
    ) {
        $titleFormattingToken->getTokenAsString()->willReturn('aInvalidJWTToken');

        $client->request('GET', 'api/data-quality-insights/title', [
                'query' => [
                    'title' => 'text'
                ],
                'headers' => [
                    'X-AKENEO-AUTH' => 'aInvalidJWTToken'
                ]
            ]
        )->willReturn($response);

        $response->getBody()->willReturn($body);
        $body->getContents()->willReturn('');

        $response->getStatusCode()->willReturn(Response::HTTP_UNAUTHORIZED);
        $logger->error('An error occurred while trying to provide a title suggestion.', [
            'http_response_status_code' => 401,
            'title' => 'text'
        ])->shouldBeCalled();

        $this->shouldThrow(UnableToProvideATitleSuggestion::class)
            ->during('format', [new ProductTitle('text')]);
    }

    public function it_returns_a_suggested_title(
        $client,
        $logger,
        $titleFormattingToken,
        ResponseInterface $response,
        StreamInterface $body
    ) {
        $titleFormattingToken->getTokenAsString()->willReturn('a.jwt.token');

        $client->request('GET', 'api/data-quality-insights/title', [
                'query' => [
                    'title' => 'text'
                ],
                'headers' => [
                    'X-AKENEO-AUTH' => 'a.jwt.token'
                ]
            ]
        )->willReturn($response);

        $response->getBody()->willReturn($body);
        $body->getContents()->willReturn('{"suggestion": "Text"}');

        $response->getStatusCode()->willReturn(Response::HTTP_OK);
        $logger->error('An error occurred while trying to provide a title suggestion.', [
            'http_response_status_code' => 200,
            'title' => 'text'
        ])->shouldNotBeCalled();

        $this->format(new ProductTitle('text'))->shouldBeLike(new ProductTitle('Text'));
    }
}
