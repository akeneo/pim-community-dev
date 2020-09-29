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
        $this->beConstructedWith($client, $validator);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(UrlReachabilityCheckerInterface::class);
    }

    public function it_checks_url_is_good_and_reachable($client, $validator): void
    {
        $validUrl = 'http://172.17.0.1:8000/webhook';
        $request = new Request($this->getWrappedObject()::POST, $validUrl);

        $client->send($request)->willReturn(new Response(200, [], null, '1.1', 'OK'));
        $validator->validate($validUrl, [new ValidatorAssert\Url(), new ValidatorAssert\NotBlank(),])->willReturn([]);

        $resultUrlReachabilityStatus = $this->check($validUrl);

        Assert::assertEquals(
            $resultUrlReachabilityStatus->getWrappedObject(),
            new UrlReachabilityStatus(true, "200 OK")
        );
    }

    public function it_checks_url_has_invalid_format(
        $validator,
        ConstraintViolationInterface $violation
    ): void {
        $notValidUrl = 'I_AM_NOT_A_VALID_URL';
        $violationList = new ConstraintViolationList([$violation->getWrappedObject()]);

        $violation->getMessage()->willReturn($notValidUrl);

        $validator->validate(
            $notValidUrl,
            [new ValidatorAssert\Url(), new ValidatorAssert\NotBlank(),]
        )->willReturn($violationList);

        $resultUrlReachabilityStatus = $this->check($notValidUrl);

        Assert::assertEquals(
            $resultUrlReachabilityStatus->getWrappedObject(),
            new UrlReachabilityStatus(false, $notValidUrl)
        );
    }

    public function it_checks_url_has_invalid_format_because_url_is_blank(
        $validator,
        ConstraintViolationInterface $violation
    ): void {
        $emptyUrl = '';
        $violationList = new ConstraintViolationList([$violation->getWrappedObject()]);

        $violation->getMessage()->willReturn($emptyUrl);

        $validator->validate(
            $emptyUrl,
            [new ValidatorAssert\Url(), new ValidatorAssert\NotBlank(),]
        )->willReturn($violationList);

        $resultUrlReachabilityStatus = $this->check($emptyUrl);

        Assert::assertEquals(
            $resultUrlReachabilityStatus->getWrappedObject(),
            new UrlReachabilityStatus(false, $emptyUrl)
        );
    }

    public function it_checks_url_is_not_reachable_and_has_response($client, $validator): void
    {
        $validUrl = 'http://172.17.0.1:8000/webhook';
        $request = new Request($this->getWrappedObject()::POST, $validUrl);
        $response = new Response(451, [], null, '1.1', 'Unavailable For Legal Reasons');
        $requestException = new RequestException('RequestException message', $request, $response);

        $client->send($request)->willThrow($requestException);
        $validator->validate($validUrl, [new ValidatorAssert\Url(), new ValidatorAssert\NotBlank(),])->willReturn([]);

        $resultUrlReachabilityStatus = $this->check($validUrl);

        Assert::assertEquals(
            $resultUrlReachabilityStatus->getWrappedObject(),
            new UrlReachabilityStatus(false, "451 Unavailable For Legal Reasons")
        );
    }

    public function it_checks_url_is_not_reachable_and_has_no_response($client, $validator): void
    {
        $validUrl = 'http://172.17.0.1:8000/webhook';
        $request = new Request($this->getWrappedObject()::POST, $validUrl);
        $connectException = new ConnectException('ConnectException message', $request);

        $client->send($request)->willThrow($connectException);
        $validator->validate($validUrl, [new ValidatorAssert\Url(), new ValidatorAssert\NotBlank(),])->willReturn([]);

        $resultUrlReachabilityStatus = $this->check($validUrl);

        Assert::assertEquals(
            $resultUrlReachabilityStatus->getWrappedObject(),
            new UrlReachabilityStatus(false, "Failed to connect to server")
        );
    }

    public function it_checks_url_is_not_reachable_and_no_request_exception_has_been_raised($client, $validator): void
    {
        $validUrl = 'http://172.17.0.1:8000/webhook';
        $request = new Request($this->getWrappedObject()::POST, $validUrl);
        $transferException = new TransferException('TransferException message');

        $client->send($request)->willThrow($transferException);
        $validator->validate($validUrl, [new ValidatorAssert\Url(), new ValidatorAssert\NotBlank(),])->willReturn([]);

        $resultUrlReachabilityStatus = $this->check($validUrl);

        Assert::assertEquals(
            $resultUrlReachabilityStatus->getWrappedObject(),
            new UrlReachabilityStatus(false, "Failed to connect to server")
        );
    }
}
