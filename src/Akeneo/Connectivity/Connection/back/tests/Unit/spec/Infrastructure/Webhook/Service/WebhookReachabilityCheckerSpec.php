<?php
declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Webhook\Service;

use Akeneo\Connectivity\Connection\Application\Webhook\Service\UrlReachabilityCheckerInterface;
use Akeneo\Connectivity\Connection\Domain\Webhook\DTO\UrlReachabilityStatus;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\RequestHeaders;
use Akeneo\Platform\Bundle\PimVersionBundle\VersionProviderInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PhpSpec\ObjectBehavior;
use PHPUnit\Framework\Assert;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WebhookReachabilityCheckerSpec extends ObjectBehavior
{
    public function let(
        ClientInterface $client,
        ValidatorInterface $validator,
        VersionProviderInterface $versionProvider,
    ): void {
        $this->beConstructedWith($client, $validator, $versionProvider, \getenv('PFID'));
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(UrlReachabilityCheckerInterface::class);
    }

    public function it_checks_url_is_good_and_reachable($client, $validator, $versionProvider): void
    {
        $versionProvider->getVersion()->willReturn('v20210526040645');

        $validUrl = 'http://172.17.0.1:8000/webhook';
        $secret = '1234';

        $client->send(Argument::that(fn ($object): bool => $object instanceof Request &&
            $object->hasHeader('Content-Type') &&
            $object->hasHeader(RequestHeaders::HEADER_REQUEST_SIGNATURE) &&
            $object->hasHeader(RequestHeaders::HEADER_REQUEST_TIMESTAMP) &&
            $object->hasHeader(RequestHeaders::HEADER_REQUEST_USERAGENT) &&
            $this->getWrappedObject()::POST === $object->getMethod() &&
            $validUrl === (string) $object->getUri()), ['allow_redirects' => false])->willReturn(new Response(200, [], null, '1.1', 'OK'));
        $validator->validate($validUrl, Argument::any())->willReturn([]);

        $resultUrlReachabilityStatus = $this->check($validUrl, $secret);

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
        $secret = '1234';
        $violationList = new ConstraintViolationList([$violation->getWrappedObject()]);

        $violation->getMessage()->willReturn($notValidUrl);

        $validator->validate(
            $notValidUrl,
            Argument::any()
        )->willReturn($violationList);

        $resultUrlReachabilityStatus = $this->check($notValidUrl, $secret);

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
        $secret = '1234';
        $violationList = new ConstraintViolationList([$violation->getWrappedObject()]);

        $violation->getMessage()->willReturn($emptyUrl);

        $validator->validate(
            $emptyUrl,
            Argument::any()
        )->willReturn($violationList);

        $resultUrlReachabilityStatus = $this->check($emptyUrl, $secret);

        Assert::assertEquals(
            $resultUrlReachabilityStatus->getWrappedObject(),
            new UrlReachabilityStatus(false, $emptyUrl)
        );
    }

    public function it_checks_url_is_good_and_reachable_but_have_301_redirect_response($client, $validator, $versionProvider): void
    {
        $versionProvider->getVersion()->willReturn('v20210526040645');

        $validUrl = 'http://172.17.0.1:8000/webhook';
        $secret = '1234';

        $client->send(Argument::that(fn ($object): bool => $object instanceof Request &&
            $object->hasHeader('Content-Type') &&
            $object->hasHeader(RequestHeaders::HEADER_REQUEST_SIGNATURE) &&
            $object->hasHeader(RequestHeaders::HEADER_REQUEST_TIMESTAMP) &&
            $object->hasHeader(RequestHeaders::HEADER_REQUEST_USERAGENT) &&
            $this->getWrappedObject()::POST === $object->getMethod() &&
            $validUrl === (string) $object->getUri()), ['allow_redirects' => false])->willReturn(new Response(301, [], null, '1.1', 'Moved Permanently'));
        $validator->validate($validUrl, Argument::any())->willReturn([]);

        $resultUrlReachabilityStatus = $this->check($validUrl, $secret);

        Assert::assertEquals(
            $resultUrlReachabilityStatus->getWrappedObject(),
            new UrlReachabilityStatus(false, '301 Server response contains a redirection. This is not allowed.')
        );
    }

    public function it_checks_url_is_not_reachable_and_has_response($client, $validator, $versionProvider): void
    {
        $versionProvider->getVersion()->willReturn('v20210526040645');

        $validUrl = 'http://172.17.0.1:8000/webhook';
        $secret = '1234';

        $request = new Request($this->getWrappedObject()::POST, $validUrl, []);
        $response = new Response(451, [], null, '1.1', 'Unavailable For Legal Reasons');
        $requestException = new RequestException('RequestException message', $request, $response);

        $client->send(Argument::that(fn ($object): bool => $object instanceof Request &&
            $object->hasHeader('Content-Type') &&
            $object->hasHeader(RequestHeaders::HEADER_REQUEST_SIGNATURE) &&
            $object->hasHeader(RequestHeaders::HEADER_REQUEST_TIMESTAMP) &&
            $object->hasHeader(RequestHeaders::HEADER_REQUEST_USERAGENT) &&
            $this->getWrappedObject()::POST === $object->getMethod() &&
            $validUrl === (string) $object->getUri()), ['allow_redirects' => false])->willThrow($requestException);
        $validator->validate($validUrl, Argument::any())->willReturn([]);

        $resultUrlReachabilityStatus = $this->check($validUrl, $secret);

        Assert::assertEquals(
            $resultUrlReachabilityStatus->getWrappedObject(),
            new UrlReachabilityStatus(false, "451 Unavailable For Legal Reasons")
        );
    }

    public function it_checks_url_is_not_reachable_and_has_no_response($client, $validator, $versionProvider): void
    {
        $versionProvider->getVersion()->willReturn('v20210526040645');

        $validUrl = 'http://172.17.0.1:8000/webhook';
        $secret = '1234';
        $request = new Request($this->getWrappedObject()::POST, $validUrl, []);
        $connectException = new ConnectException('ConnectException message', $request);

        $client->send(Argument::that(fn ($object): bool => $object instanceof Request &&
            $object->hasHeader('Content-Type') &&
            $object->hasHeader(RequestHeaders::HEADER_REQUEST_SIGNATURE) &&
            $object->hasHeader(RequestHeaders::HEADER_REQUEST_TIMESTAMP) &&
            $object->hasHeader(RequestHeaders::HEADER_REQUEST_USERAGENT) &&
            $this->getWrappedObject()::POST === $object->getMethod() &&
            $validUrl === (string) $object->getUri()), ['allow_redirects' => false])->willThrow($connectException);
        $validator->validate($validUrl, Argument::any())->willReturn([]);

        $resultUrlReachabilityStatus = $this->check($validUrl, $secret);

        Assert::assertEquals(
            $resultUrlReachabilityStatus->getWrappedObject(),
            new UrlReachabilityStatus(false, "Failed to connect to server")
        );
    }

    public function it_checks_url_is_not_reachable_and_no_request_exception_has_been_raised($client, $validator, $versionProvider): void
    {
        $versionProvider->getVersion()->willReturn('v20210526040645');

        $validUrl = 'http://172.17.0.1:8000/webhook';
        $secret = '1234';
        $transferException = new TransferException('TransferException message');

        $client->send(Argument::that(fn ($object): bool => $object instanceof Request &&
            $object->hasHeader('Content-Type') &&
            $object->hasHeader(RequestHeaders::HEADER_REQUEST_SIGNATURE) &&
            $object->hasHeader(RequestHeaders::HEADER_REQUEST_TIMESTAMP) &&
            $object->hasHeader(RequestHeaders::HEADER_REQUEST_USERAGENT) &&
            $this->getWrappedObject()::POST === $object->getMethod() &&
            $validUrl === (string) $object->getUri()), ['allow_redirects' => false])->willThrow($transferException);
        $validator->validate($validUrl, Argument::any())->willReturn([]);

        $resultUrlReachabilityStatus = $this->check($validUrl, $secret);

        Assert::assertEquals(
            $resultUrlReachabilityStatus->getWrappedObject(),
            new UrlReachabilityStatus(false, "Failed to connect to server")
        );
    }
}
