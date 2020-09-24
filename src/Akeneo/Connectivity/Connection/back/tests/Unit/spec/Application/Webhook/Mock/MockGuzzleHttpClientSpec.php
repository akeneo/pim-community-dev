<?php
declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Webhook\Mock;

use Akeneo\Connectivity\Connection\Application\Webhook\Mock\MockGuzzleHttpClient;
use GuzzleHttp\Exception\RequestException;
use PhpSpec\ObjectBehavior;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Assert;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MockGuzzleHttpClientSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith();
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(MockGuzzleHttpClient::class);
    }

    public function it_calls_mock_response_200(): void
    {
        $result = $this->send(new Request('POST', 'http://www.get-response-200.com'));

        Assert::assertInstanceOf(Response::class, $result->getWrappedObject());
        Assert::assertEquals(200, $result->getWrappedObject()->getStatusCode());
        Assert::assertEquals('OK', $result->getWrappedObject()->getReasonPhrase());
    }

    public function it_calls_mock_response_451(): void
    {
        $this
            ->shouldThrow(RequestException::class)
            ->during('send', [new Request('POST', 'http://www.get-response-451.com')]);
    }

    public function it_calls_mock_response_500(): void
    {
        $this
            ->shouldThrow(RequestException::class)
            ->during('send', [new Request('POST', 'http://www.get-response-500.com')]);
    }

    public function it_calls_mock_unknown_response(): void
    {
        $this
            ->shouldThrow(RequestException::class)
            ->during('send', [new Request('POST', 'http://www.unreachable-url.com')]);
    }

}