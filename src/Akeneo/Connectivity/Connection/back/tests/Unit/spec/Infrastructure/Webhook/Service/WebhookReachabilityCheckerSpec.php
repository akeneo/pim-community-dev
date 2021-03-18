<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Webhook\Service;

use Akeneo\Connectivity\Connection\Application\Webhook\Service\UrlReachabilityCheckerInterface;
use Akeneo\Connectivity\Connection\Application\Webhook\Validation\NotPrivateNetworkUrl;
use Akeneo\Connectivity\Connection\Domain\Webhook\DTO\UrlReachabilityStatus;
use Akeneo\Connectivity\Connection\Infrastructure\Service\Clock\FakeClock;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\Client\Signature;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\RequestHeaders;
use GuzzleHttp\ClientInterface;
use PhpSpec\ObjectBehavior;
use GuzzleHttp\Psr7\Response;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WebhookReachabilityCheckerSpec extends ObjectBehavior
{
    public function let(
        ClientInterface $client,
        ValidatorInterface $validator
    ): void {
        $this->beConstructedWith(
            $client,
            $validator,
            new FakeClock(new \DateTimeImmutable('@1577836800'))
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(UrlReachabilityCheckerInterface::class);
    }

    public function it_validates_the_url(
        $validator,
        ConstraintViolationInterface $violation
    ): void {
        $violation->getMessage()
            ->willReturn('error_message');
        $violationList = new ConstraintViolationList([$violation->getWrappedObject()]);

        $validator->validate('invalid_url', [
            new Assert\NotBlank(),
            new Assert\Url(),
            new NotPrivateNetworkUrl()
        ])->willReturn($violationList);

        $this->check('invalid_url', 'secret')
            ->shouldBeLike(new UrlReachabilityStatus(false, 'error_message'));
    }

    public function it_sends_the_request($client, $validator): void
    {
        $request = new Request('POST', 'http://valid-url.test', [
            'Content-Type' => 'application/json',
            RequestHeaders::HEADER_REQUEST_SIGNATURE => Signature::createSignature('secret', 1577836800),
            RequestHeaders::HEADER_REQUEST_TIMESTAMP => 1577836800,
        ]);
        $options = ['allow_redirects' => false];

        $validator->validate(Argument::cetera())
            ->willReturn([]);
        $client->send($request, $options)
            ->shouldBeCalled()
            ->willReturn(new Response());

        $this->check('http://valid-url.test', 'secret');
    }

    public function it_succeeds_when_the_response_is_a_success($client, $validator): void
    {
        $validator->validate(Argument::cetera())
            ->willReturn([]);
        $client->send(Argument::cetera())
            ->willReturn(new Response(200, [], null, '1.1', 'OK'));

        $this->check('http://valid-url.test', 'secret')
            ->shouldBeLike(new UrlReachabilityStatus(true, '200 OK'));
    }

    public function it_fails_when_the_response_is_an_error(
        $client,
        $validator,
        RequestInterface $request
    ): void {
        $response = new Response(451, [], null, '1.1', 'Unavailable For Legal Reasons');
        $requestException = new RequestException('RequestException message', $request->getWrappedObject(), $response);

        $validator->validate(Argument::cetera())
            ->willReturn([]);
        $client->send(Argument::cetera())
            ->willThrow($requestException);

        $this->check('http://valid-url.test', 'secret')
            ->shouldBeLike(new UrlReachabilityStatus(false, '451 Unavailable For Legal Reasons'));
    }

    public function it_fails_when_the_connection_can_not_be_established(
        $client,
        $validator,
        RequestInterface $request
    ): void {
        $connectException = new ConnectException('ConnectException message', $request->getWrappedObject());

        $validator->validate(Argument::cetera())
            ->willReturn([]);
        $client->send(Argument::cetera())
            ->willThrow($connectException);

        $this->check('http://valid-url.test', 'secret')
            ->shouldBeLike(new UrlReachabilityStatus(false, 'Failed to connect to server'));
    }

    public function it_fails_when_an_unknown_error_is_raised($client, $validator): void
    {
        $transferException = new TransferException('TransferException message');

        $validator->validate(Argument::cetera())
            ->willReturn([]);
        $client->send(Argument::cetera())
            ->willThrow($transferException);

        $this->check('http://valid-url.test', 'secret')
            ->shouldBeLike(new UrlReachabilityStatus(false, 'Failed to connect to server'));
    }

    public function it_fails_when_the_request_is_redirected($client, $validator)
    {
        $response = new Response(301, [], null, '1.1', 'Moved Permanently');

        $validator->validate(Argument::cetera())
            ->willReturn([]);
        $client->send(Argument::cetera())
            ->willReturn($response);

        $this->check('http://valid-url.test', 'secret')
            ->shouldBeLike(new UrlReachabilityStatus(false, '301 Moved Permanently. Redirection are not allowed.'));
    }
}
