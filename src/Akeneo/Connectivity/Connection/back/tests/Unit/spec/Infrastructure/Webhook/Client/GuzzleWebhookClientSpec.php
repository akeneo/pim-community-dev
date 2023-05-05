<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Webhook\Client;

use Akeneo\Connectivity\Connection\Application\Webhook\Service\EventsApiRequestLoggerInterface;
use Akeneo\Connectivity\Connection\Application\Webhook\Service\Logger\SendApiEventRequestLogger;
use Akeneo\Connectivity\Connection\Domain\Webhook\Client\WebhookClientInterface;
use Akeneo\Connectivity\Connection\Domain\Webhook\Client\WebhookRequest;
use Akeneo\Connectivity\Connection\Domain\Webhook\Event\EventsApiRequestFailedEvent;
use Akeneo\Connectivity\Connection\Domain\Webhook\Event\EventsApiRequestSucceededEvent;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Read\ActiveWebhook;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\WebhookEvent;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\Client\GuzzleWebhookClient;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\Client\Signature;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\RequestHeaders;
use Akeneo\Platform\Bundle\PimVersionBundle\VersionProviderInterface;
use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\EventQueue\Event;
use Akeneo\Platform\Component\EventQueue\EventInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PhpSpec\ObjectBehavior;
use PHPUnit\Framework\Assert;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GuzzleWebhookClientSpec extends ObjectBehavior
{
    public function let(
        SendApiEventRequestLogger $sendApiEventRequestLogger,
        EventsApiRequestLoggerInterface $eventsApiRequestLogger,
        EventDispatcherInterface $eventDispatcher,
        VersionProviderInterface $versionProvider,
    ): void {
        $eventDispatcher->dispatch(Argument::any())->willReturn(Argument::type('object'));
        $this->beConstructedWith(
            new Client(),
            new JsonEncoder(),
            $sendApiEventRequestLogger,
            $eventsApiRequestLogger,
            $eventDispatcher,
            ['timeout' => 0.5, 'concurrency' => 1],
            $versionProvider,
            \getenv('PFID'),
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(GuzzleWebhookClient::class);
        $this->shouldImplement(WebhookClientInterface::class);
    }

    public function it_sends_webhook_requests_in_bulk(
        SendApiEventRequestLogger $sendApiEventRequestLogger,
        EventsApiRequestLoggerInterface $eventsApiRequestLogger,
        EventDispatcherInterface $eventDispatcher,
        VersionProviderInterface $versionProvider,
    ): void {
        $eventDispatcher->dispatch(Argument::any())->willReturn(Argument::type('object'));
        $versionProvider->getVersion()->willReturn('v20210526040645');

        $mock = new MockHandler(
            [
                new Response(200, ['Content-Length' => 0]),
                new Response(200, ['Content-Length' => 0]),
            ]
        );
        $container = [];
        $history = Middleware::history($container);

        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);

        $this->beConstructedWith(
            new Client(['handler' => $handlerStack]),
            new JsonEncoder(),
            $sendApiEventRequestLogger,
            $eventsApiRequestLogger,
            $eventDispatcher,
            ['timeout' => 0.5, 'concurrency' => 1],
            $versionProvider,
            \getenv('PFID'),
        );

        $author = Author::fromNameAndType('julia', Author::TYPE_UI);
        $Request1pimEvent = $this->createEvent($author, ['data_1'], 1577836800, '7abae2fe-759a-4fce-aa43-f413980671b3');
        $request1 = new WebhookRequest(
            new ActiveWebhook('ecommerce', 0, 'a_secret', 'http://localhost/webhook1', false),
            [
                new WebhookEvent(
                    'product.created',
                    '7abae2fe-759a-4fce-aa43-f413980671b3',
                    '2020-01-01T00:00:00+00:00',
                    $author,
                    'staging.akeneo.com',
                    ['data_1'],
                    $Request1pimEvent
                ),
            ]
        );

        $Request2pimEvent = $this->createEvent($author, ['data_2'], 1577836800, '7abae2fe-759a-4fce-aa43-f413980671b3');
        $request2 = new WebhookRequest(
            new ActiveWebhook('erp', 1, 'a_secret', 'http://localhost/webhook2', false),
            [
                new WebhookEvent(
                    'product.created',
                    '7abae2fe-759a-4fce-aa43-f413980671b3',
                    '2020-01-01T00:00:00+00:00',
                    $author,
                    'staging.akeneo.com',
                    ['data_2'],
                    $Request2pimEvent
                ),
            ]
        );

        $this->bulkSend([$request1, $request2]);

        Assert::assertCount(2, $container);

        // Request 1

        $request = $this->findRequest($container, 'http://localhost/webhook1');

        Assert::assertNotNull($request);

        $body = '{"events":[{"action":"product.created","event_id":"7abae2fe-759a-4fce-aa43-f413980671b3","event_datetime":"2020-01-01T00:00:00+00:00","author":"julia","author_type":"ui","pim_source":"staging.akeneo.com","data":["data_1"]}]}';
        Assert::assertEquals($body, (string)$request->getBody());

        $timestamp = (int)$request->getHeader(RequestHeaders::HEADER_REQUEST_TIMESTAMP)[0];
        $signature = Signature::createSignature('a_secret', $timestamp, $body);
        Assert::assertEquals($signature, $request->getHeader(RequestHeaders::HEADER_REQUEST_SIGNATURE)[0]);

        $userAgent = 'AkeneoPIM/v20210526040645';
        if (false !== \getenv('PFID')) {
            $userAgent .= ' '.\getenv('PFID');
        }

        Assert::assertSame($userAgent, $request->getHeader(RequestHeaders::HEADER_REQUEST_USERAGENT)[0]);

        $eventDispatcher
            ->dispatch(Argument::allOf(
                Argument::type(EventsApiRequestSucceededEvent::class),
                Argument::that(function (EventsApiRequestSucceededEvent $event) use ($Request1pimEvent): bool {
                    if ('ecommerce' !== $event->getConnectionCode() || $Request1pimEvent !== $event->getEvents()[0]) {
                        return false;
                    }

                    return true;
                })
            ))
            ->shouldBeCalledTimes(1);

        $eventsApiRequestLogger->logEventsApiRequestSucceed(
            'ecommerce',
            $request1->apiEvents(),
            'http://localhost/webhook1',
            200,
            Argument::any()
        )->shouldBeCalled();

        $eventDispatcher->dispatch(Argument::type(EventsApiRequestSucceededEvent::class))->shouldBeCalled();

        // Request 2

        $request = $this->findRequest($container, 'http://localhost/webhook2');
        Assert::assertNotNull($request);

        $body = '{"events":[{"action":"product.created","event_id":"7abae2fe-759a-4fce-aa43-f413980671b3","event_datetime":"2020-01-01T00:00:00+00:00","author":"julia","author_type":"ui","pim_source":"staging.akeneo.com","data":["data_2"]}]}';
        Assert::assertEquals($body, (string)$request->getBody());

        $timestamp = (int)$request->getHeader(RequestHeaders::HEADER_REQUEST_TIMESTAMP)[0];
        $signature = Signature::createSignature('a_secret', $timestamp, $body);
        Assert::assertEquals($signature, $request->getHeader(RequestHeaders::HEADER_REQUEST_SIGNATURE)[0]);

        $eventDispatcher
            ->dispatch(Argument::allOf(
                Argument::type(EventsApiRequestSucceededEvent::class),
                Argument::that(function (EventsApiRequestSucceededEvent $event) use ($Request2pimEvent): bool {
                    if ('erp' !== $event->getConnectionCode() || $Request2pimEvent !== $event->getEvents()[0]) {
                        return false;
                    }

                    return true;
                })
            ))
            ->shouldBeCalledTimes(1);

        $eventsApiRequestLogger->logEventsApiRequestSucceed(
            'erp',
            $request2->apiEvents(),
            'http://localhost/webhook2',
            200,
            Argument::any()
        )->shouldBeCalled();

        $eventDispatcher->dispatch(Argument::type(EventsApiRequestSucceededEvent::class))->shouldBeCalled();
    }

    public function it_logs_a_failed_events_api_request(
        SendApiEventRequestLogger $sendApiEventRequestLogger,
        EventsApiRequestLoggerInterface $eventsApiRequestLogger,
        EventDispatcherInterface $eventDispatcher,
        VersionProviderInterface $versionProvider,
    ): void {
        $versionProvider->getVersion()->willReturn('v20210526040645');

        $mock = new MockHandler(
            [
                new Response(500, ['Content-Length' => 0]),
            ]
        );
        $container = [];
        $history = Middleware::history($container);

        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);

        $this->beConstructedWith(
            new Client(['handler' => $handlerStack]),
            new JsonEncoder(),
            $sendApiEventRequestLogger,
            $eventsApiRequestLogger,
            $eventDispatcher,
            ['timeout' => 0.5, 'concurrency' => 1],
            $versionProvider,
            \getenv('PFID'),
        );

        $author = Author::fromNameAndType('julia', Author::TYPE_UI);
        $request1 = new WebhookRequest(
            new ActiveWebhook('ecommerce', 0, 'a_secret', 'http://localhost/webhook1', false),
            [
                new WebhookEvent(
                    'product.created',
                    '7abae2fe-759a-4fce-aa43-f413980671b3',
                    '2020-01-01T00:00:00+00:00',
                    $author,
                    'staging.akeneo.com',
                    ['data_1'],
                    $this->createEvent($author, ['data_1'], 1577836800, '7abae2fe-759a-4fce-aa43-f413980671b3')
                ),
            ]
        );

        $this->bulkSend([$request1]);

        Assert::assertCount(1, $container);

        // Request 1
        $eventsApiRequestLogger->logEventsApiRequestFailed(
            'ecommerce',
            $request1->apiEvents(),
            'http://localhost/webhook1',
            500,
            Argument::any()
        )->shouldBeCalled();

        $eventDispatcher->dispatch(Argument::type(EventsApiRequestFailedEvent::class))->shouldBeCalled();
    }

    public function it_does_not_send_webhook_request_because_of_timeout(
        SendApiEventRequestLogger $sendApiEventRequestLogger,
        EventsApiRequestLoggerInterface $debugLogger,
        EventDispatcherInterface $eventDispatcher,
        VersionProviderInterface $versionProvider,
    ): void {
        $versionProvider->getVersion()->willReturn('v20210526040645');

        $container = [];
        $history = Middleware::history($container);

        $handlerStack = HandlerStack::create();
        $handlerStack->push($history);

        $this->beConstructedWith(
            new Client(['handler' => $handlerStack]),
            new JsonEncoder(),
            $sendApiEventRequestLogger,
            $debugLogger,
            $eventDispatcher,
            ['timeout' => 0.5, 'concurrency' => 1],
            $versionProvider,
            \getenv('PFID'),
        );

        $author = Author::fromNameAndType('julia', Author::TYPE_UI);
        $request = new WebhookRequest(
            new ActiveWebhook('ecommerce', 0, 'a_secret', 'http://localhost/webhook', false),
            [
                new WebhookEvent(
                    'product.created',
                    '7abae2fe-759a-4fce-aa43-f413980671b3',
                    '2020-01-01T00:00:00+00:00',
                    $author,
                    'staging.akeneo.com',
                    ['data_1'],
                    $this->createEvent($author, ['data_1'], 1577836800, '7abae2fe-759a-4fce-aa43-f413980671b3')
                ),
            ]
        );

        $this->bulkSend([$request]);

        $debugLogger->logEventsApiRequestTimedOut(
            'ecommerce',
            $request->apiEvents(),
            'http://localhost/webhook',
            0.5
        )->shouldBeCalled();

        $eventDispatcher->dispatch(Argument::type(EventsApiRequestFailedEvent::class))->shouldBeCalled();

        Assert::assertCount(1, $container);

        $request = $this->findRequest($container, 'http://localhost/webhook');

        Assert::assertNotNull($request);

        $body = '{"events":[{"action":"product.created","event_id":"7abae2fe-759a-4fce-aa43-f413980671b3","event_datetime":"2020-01-01T00:00:00+00:00","author":"julia","author_type":"ui","pim_source":"staging.akeneo.com","data":["data_1"]}]}';
        Assert::assertEquals($body, (string)$request->getBody());

        $timestamp = (int)$request->getHeader(RequestHeaders::HEADER_REQUEST_TIMESTAMP)[0];
        $signature = Signature::createSignature('a_secret', $timestamp, $body);
        Assert::assertEquals($signature, $request->getHeader(RequestHeaders::HEADER_REQUEST_SIGNATURE)[0]);
    }

    private function findRequest(array $container, string $url): ?Request
    {
        foreach ($container as $transaction) {
            if ($url === (string)$transaction['request']->getUri()) {
                return $transaction['request'];
            }
        }

        return null;
    }

    private function createEvent(Author $author, array $data, int $timestamp, string $uuid): EventInterface
    {
        return new class($author, $data, $timestamp, $uuid) extends Event {
            public function getName(): string
            {
                return 'product.created';
            }
        };
    }
}
