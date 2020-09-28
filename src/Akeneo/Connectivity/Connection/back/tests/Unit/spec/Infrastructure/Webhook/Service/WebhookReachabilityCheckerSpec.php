<?php
declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Webhook\Service;

use Akeneo\Connectivity\Connection\Application\Webhook\Service\UrlReachabilityCheckerInterface;
use Akeneo\Connectivity\Connection\Domain\Webhook\DTO\UrlReachabilityStatus;
use GuzzleHttp\ClientInterface;
use PhpSpec\ObjectBehavior;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as ValidatorAssert;
use PHPUnit\Framework\Assert;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Exception\RequestException;

class WebhookReachabilityCheckerSpec extends ObjectBehavior
{
    public function let(
        ClientInterface $client,
        ValidatorInterface $validator
    ): void {
        $this->beConstructedWith($client, $validator);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(UrlReachabilityCheckerInterface::class);
    }

    public function it_checks_url_is_good_and_reachable($client, $validator): void
    {
        $validUrl = 'http://172.17.0.1:8000/webhook';
        $ExpectedReachabilityStatus = new UrlReachabilityStatus(true, "200 OK");
        $request = new Request($this->getWrappedObject()::POST, $validUrl);

        $client->send($request)->willReturn(new Response(200, [], null, '1.1', 'OK'));
        $validator->validate($validUrl, [new ValidatorAssert\Url(), new ValidatorAssert\NotBlank(),])->willReturn([]);

        $resultUrlReachabilityStatus = $this->check($validUrl);

        Assert::assertEquals($resultUrlReachabilityStatus->getWrappedObject(), $ExpectedReachabilityStatus);
    }

    public function it_checks_url_has_invalid_format(
        $validator,
        ConstraintViolationInterface $violation
    ): void {
        $notValidUrl = 'I_AM_NOT_AN_URL';
        $ExpectedReachabilityStatus = new UrlReachabilityStatus(false, $this->getWrappedObject()::WRONG_URL);
        $violationList = new ConstraintViolationList([$violation->getWrappedObject()]);

        $validator->validate(
            $notValidUrl,
            [new ValidatorAssert\Url(), new ValidatorAssert\NotBlank(),]
        )->willReturn($violationList);

        $resultUrlReachabilityStatus = $this->check($notValidUrl);

        Assert::assertEquals(
            $resultUrlReachabilityStatus->getWrappedObject()->normalize(),
            $ExpectedReachabilityStatus->normalize()
        );
    }

    public function it_checks_url_has_invalid_format_because_url_is_blank(
        $validator,
        ConstraintViolationInterface $violation
    ): void {
        $notValidUrl = '';
        $ExpectedReachabilityStatus = new UrlReachabilityStatus(false, $this->getWrappedObject()::WRONG_URL);
        $violationList = new ConstraintViolationList([$violation->getWrappedObject()]);

        $validator->validate(
            $notValidUrl,
            [new ValidatorAssert\Url(), new ValidatorAssert\NotBlank(),]
        )->willReturn($violationList);

        $resultUrlReachabilityStatus = $this->check($notValidUrl);

        Assert::assertEquals(
            $resultUrlReachabilityStatus->getWrappedObject()->normalize(),
            $ExpectedReachabilityStatus->normalize()
        );
    }

    public function it_checks_url_is_not_reachable_and_has_response($client, $validator): void
    {
        $validUrl = 'http://172.17.0.1:8000/webhook';
        $ExpectedReachabilityStatus = new UrlReachabilityStatus(false, "451 Unavailable For Legal Reasons");
        $request = new Request($this->getWrappedObject()::POST, $validUrl);
        $response = new Response(451, [], null, '1.1', 'Unavailable For Legal Reasons');
        $requestException = new RequestException('RequestException message', $request, $response);

        $client->send($request)->willThrow($requestException);
        $validator->validate($validUrl, [new ValidatorAssert\Url(), new ValidatorAssert\NotBlank(),])->willReturn([]);

        $resultUrlReachabilityStatus = $this->check($validUrl);

        Assert::assertEquals($resultUrlReachabilityStatus->getWrappedObject(), $ExpectedReachabilityStatus);
    }

    public function it_checks_url_is_not_reachable_and_has_no_response($client, $validator): void
    {
        $validUrl = 'http://172.17.0.1:8000/webhook';
        $ExpectedReachabilityStatus = new UrlReachabilityStatus(false, "Failed to connect to server");
        $request = new Request($this->getWrappedObject()::POST, $validUrl);
        $connectException = new ConnectException('ConnectException message', $request);

        $client->send($request)->willThrow($connectException);
        $validator->validate($validUrl, [new ValidatorAssert\Url(), new ValidatorAssert\NotBlank(),])->willReturn([]);

        $resultUrlReachabilityStatus = $this->check($validUrl);

        Assert::assertEquals($resultUrlReachabilityStatus->getWrappedObject(), $ExpectedReachabilityStatus);
    }

    public function it_checks_url_is_not_reachable_and_no_request_exception_has_been_raised($client, $validator): void
    {
        $validUrl = 'http://172.17.0.1:8000/webhook';
        $ExpectedReachabilityStatus = new UrlReachabilityStatus(false, "Failed to connect to server");
        $request = new Request($this->getWrappedObject()::POST, $validUrl);
        $transferException = new TransferException('TransferException message');

        $client->send($request)->willThrow($transferException);
        $validator->validate($validUrl, [new ValidatorAssert\Url(), new ValidatorAssert\NotBlank(),])->willReturn([]);

        $resultUrlReachabilityStatus = $this->check($validUrl);

        Assert::assertEquals($resultUrlReachabilityStatus->getWrappedObject(), $ExpectedReachabilityStatus);
    }
}
