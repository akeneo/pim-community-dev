<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Webhook\Command;

use Akeneo\Connectivity\Connection\Application\Webhook\Command\SendBusinessEventToWebhooksCommand;
use Akeneo\Connectivity\Connection\Application\Webhook\Command\SendBusinessEventToWebhooksHandler;
use Akeneo\Connectivity\Connection\Application\Webhook\Log\EventSubscriptionEventBuildLog;
use Akeneo\Connectivity\Connection\Application\Webhook\Log\EventSubscriptionRequestsLimitReachedLog;
use Akeneo\Connectivity\Connection\Application\Webhook\Service\CacheClearerInterface;
use Akeneo\Connectivity\Connection\Application\Webhook\WebhookEventBuilder;
use Akeneo\Connectivity\Connection\Application\Webhook\WebhookUserAuthenticator;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Query\CountHourlyEventsApiRequestQuery;
use Akeneo\Connectivity\Connection\Domain\Webhook\Client\WebhookClient;
use Akeneo\Connectivity\Connection\Domain\Webhook\Client\WebhookRequest;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Read\ActiveWebhook;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\WebhookEvent;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\SelectActiveWebhooksQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Persistence\Dbal\Repository\DbalEventsApiRequestCountRepository;
use Akeneo\Platform\Component\EventQueue\Event;
use Akeneo\Platform\Component\EventQueue\EventInterface;
use Akeneo\UserManagement\Component\Model\User;
use PhpSpec\ObjectBehavior;
use PHPUnit\Framework\Assert;
use Prophecy\Argument;
use Psr\Log\NullLogger;
use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\EventQueue\BulkEvent;
use Psr\Log\LoggerInterface;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class SendBusinessEventToWebhooksHandlerSpec extends ObjectBehavior
{
    public function let(
        SelectActiveWebhooksQuery $selectActiveWebhooksQuery,
        WebhookUserAuthenticator $webhookUserAuthenticator,
        WebhookClient $client,
        WebhookEventBuilder $builder,
        DbalEventsApiRequestCountRepository $eventsApiRequestRepository,
        CacheClearerInterface $cacheClearer,
        CountHourlyEventsApiRequestQuery $countHourlyEventsApiRequestQuery
        CacheClearerInterface $cacheClearer
    ): void {
        $this->beConstructedWith(
            $selectActiveWebhooksQuery,
            $webhookUserAuthenticator,
            $client,
            $builder,
            new NullLogger(),
            $eventsApiRequestRepository,
            $cacheClearer,
            $countHourlyEventsApiRequestQuery,
            'staging.akeneo.com',
            666
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(SendBusinessEventToWebhooksHandler::class);
    }

    public function it_sends_message_to_webhooks(
        $selectActiveWebhooksQuery,
        $webhookUserAuthenticator,
        $client,
        $builder,
        $cacheClearer,
        $countHourlyEventsApiRequestQuery,
        $eventsApiRequestRepository
    ): void {
        $juliaUser = new User();
        $juliaUser->setId(0);
        $juliaUser->setUsername('julia');
        $juliaUser->setFirstName('Julia');
        $juliaUser->setLastName('Doe');
        $magentoUser = new User();
        $magentoUser->setId(42);
        $magentoUser->setUsername('magento_452');
        $magentoUser->setFirstName('magento_452');
        $magentoUser->setLastName('magento_452');
        $magentoUser->defineAsApiUser();

        $author = Author::fromUser($juliaUser);
        $businessEvent = $this->createEvent($author, ['data']);
        $command = new SendBusinessEventToWebhooksCommand($businessEvent);
        $webhook = new ActiveWebhook('ecommerce', 42, 'a_secret', 'http://localhost/');

        $countHourlyEventsApiRequestQuery->execute(Argument::any())->willReturn(1);

        $selectActiveWebhooksQuery->execute()->willReturn([$webhook]);

        $webhookUserAuthenticator->authenticate(42)->willReturn($magentoUser);
        $builder
            ->build(
                $businessEvent,
                [
                    'user' => $magentoUser,
                    'pim_source' => 'staging.akeneo.com',
                    'connection_code' => $webhook->connectionCode(),
                ]
            )
            ->willReturn(
                [
                    new WebhookEvent(
                        'product.created',
                        '5d30d0f6-87a6-45ad-ba6b-3a302b0d328c',
                        '2020-01-01T00:00:00+00:00',
                        $author,
                        'staging.akeneo.com',
                        ['data'],
                    ),
                ]
            );

        $eventsApiRequestRepository->upsert(Argument::any(), Argument::any())->shouldBeCalled();

        $client
            ->bulkSend(
                Argument::that(
                    function (iterable $iterable) {
                        $requests = iterator_to_array($iterable);

                        Assert::assertCount(1, $requests);
                        Assert::assertContainsOnlyInstancesOf(WebhookRequest::class, $requests);

                        Assert::assertEquals('http://localhost/', $requests[0]->url());
                        Assert::assertEquals('a_secret', $requests[0]->secret());
                        Assert::assertEquals(
                            [
                                'events' => [
                                    [
                                        'action' => 'product.created',
                                        'event_id' => '5d30d0f6-87a6-45ad-ba6b-3a302b0d328c',
                                        'event_date' => '2020-01-01T00:00:00+00:00',
                                        'author' => 'julia',
                                        'author_type' => 'ui',
                                        'pim_source' => 'staging.akeneo.com',
                                        'data' => ['data'],
                                    ],
                                ],
                            ],
                            $requests[0]->content(),
                        );

                        return true;
                    }
                ),
            )
            ->shouldBeCalled();
        $cacheClearer->clear()->shouldBeCalled();

        $this->handle($command);
    }

    public function it_does_not_send_the_message_if_the_webhook_is_the_author_of_the_business_event(
        $selectActiveWebhooksQuery,
        $webhookUserAuthenticator,
        $client,
        $builder,
        $countHourlyEventsApiRequestQuery,
        $cacheClearer,
        $eventsApiRequestRepository
    ): void {
        $erpUser = new User();
        $erpUser->setId(42);
        $erpUser->setUsername('erp_452');
        $erpUser->setFirstName('erp_452');
        $erpUser->setLastName('erp_452');
        $erpUser->defineAsApiUser();
        $magentoUser = new User();
        $magentoUser->setId(12);
        $magentoUser->setUsername('magento_987');
        $magentoUser->setFirstName('magento_987');
        $magentoUser->setLastName('magento_987');
        $magentoUser->defineAsApiUser();

        $erpAuthor = Author::fromUser($erpUser);
        $businessEvent = $this->createEvent($erpAuthor, ['data']);
        $command = new SendBusinessEventToWebhooksCommand($businessEvent);
        $erpWebhook = new ActiveWebhook('erp_source', 42, 'a_secret', 'http://localhost/');
        $magentoWebhook = new ActiveWebhook('ecommerce_destination', 12, 'a_secret', 'http://localhost/');

        $countHourlyEventsApiRequestQuery->execute(Argument::any())->willReturn(1);
        $selectActiveWebhooksQuery->execute()->willReturn([$erpWebhook, $magentoWebhook]);
        $webhookUserAuthenticator->authenticate(12)->willReturn($magentoUser);
        $webhookUserAuthenticator->authenticate(42)->willReturn($erpUser);

        $builder
            ->build(
                $businessEvent,
                [
                    'pim_source' => 'staging.akeneo.com',
                    'user' => $magentoUser,
                    'connection_code' => $magentoWebhook->connectionCode(),
                ]
            )
            ->willReturn(
                [
                    new WebhookEvent(
                        'product.created',
                        '5d30d0f6-87a6-45ad-ba6b-3a302b0d328c',
                        '2020-01-01T00:00:00+00:00',
                        $erpAuthor,
                        'staging.akeneo.com',
                        ['data'],
                    ),
                ]
            );

        $eventsApiRequestRepository->upsert(Argument::any(), Argument::any())->shouldBeCalled();

        $client
            ->bulkSend(
                Argument::that(
                    function (iterable $iterable) {
                        $requests = iterator_to_array($iterable);

                        Assert::assertCount(1, $requests);
                        Assert::assertContainsOnlyInstancesOf(WebhookRequest::class, $requests);

                        Assert::assertEquals('http://localhost/', $requests[0]->url(), 'Url is not equal');
                        Assert::assertEquals('a_secret', $requests[0]->secret(), 'Secret is not equal');
                        Assert::assertEquals(
                            [
                                'events' => [
                                    [
                                        'action' => 'product.created',
                                        'event_id' => '5d30d0f6-87a6-45ad-ba6b-3a302b0d328c',
                                        'event_date' => '2020-01-01T00:00:00+00:00',
                                        'author' => 'erp_452',
                                        'author_type' => 'api',
                                        'pim_source' => 'staging.akeneo.com',
                                        'data' => ['data'],
                                    ],
                                ],
                            ],
                            $requests[0]->content(),
                            'Content is not equal',
                        );

                        return true;
                    }
                ),
            )
            ->shouldBeCalled();

        $cacheClearer->clear()->shouldBeCalled();

        $this->handle($command);
    }

    public function it_handles_error_gracefully(
        $selectActiveWebhooksQuery,
        $webhookUserAuthenticator,
        $client,
        $builder,
        $countHourlyEventsApiRequestQuery,
        $cacheClearer,
        $eventsApiRequestRepository
    ): void {
        $user = new User();
        $user->setId(0);
        $user->setUsername('julia');
        $user->setFirstName('Julia');
        $user->setLastName('Doe');

        $author = Author::fromUser($user);
        $businessEvent = $this->createEvent($author, ['data']);
        $command = new SendBusinessEventToWebhooksCommand($businessEvent);
        $webhook = new ActiveWebhook('ecommerce', 0, 'a_secret', 'http://localhost/');

        $countHourlyEventsApiRequestQuery->execute(Argument::any())->willReturn(1);
        $selectActiveWebhooksQuery->execute()->willReturn([$webhook]);
        $webhookUserAuthenticator->authenticate(0)->willReturn($user);
        $builder
            ->build(
                $businessEvent,
                [
                    'pim_source' => 'staging.akeneo.com',
                    'user' => $user,
                    'connection_code' => $webhook->connectionCode(),
                ]
            )
            ->willThrow(\Exception::class);

        $eventsApiRequestRepository->upsert(Argument::any(), Argument::any())->shouldBeCalled();

        $client
            ->bulkSend(
                Argument::that(
                    function (iterable $iterable) {
                        $requests = iterator_to_array($iterable);

                        Assert::assertCount(0, $requests);

                        return true;
                    }
                ),
            )
            ->shouldBeCalled();
        $cacheClearer->clear()->shouldBeCalled();

        $this->handle($command);
    }

    public function it_logs_when_hourly_events_api_request_limit_is_reached(
        $selectActiveWebhooksQuery,
        $webhookUserAuthenticator,
        $client,
        $builder,
        $connectionUserForFakeSubscription,
        $eventsApiRequestRepository,
        $countHourlyEventsApiRequestQuery,
        $cacheClearer,
        LoggerInterface $logger
    ): void {

        $webhookRequestsLimit = 1;

        $this->beConstructedWith(
            $selectActiveWebhooksQuery,
            $webhookUserAuthenticator,
            $client,
            $builder,
            $logger,
            $connectionUserForFakeSubscription,
            $eventsApiRequestRepository,
            $cacheClearer,
            $countHourlyEventsApiRequestQuery,
            'staging.akeneo.com',
            $webhookRequestsLimit,
        );

        $author = Author::fromNameAndType('julia', Author::TYPE_UI);
        $bulkEvent = new BulkEvent(
            [
                $this->createEvent($author, ['data']),
            ]
        );

        $countHourlyEventsApiRequestQuery->execute(Argument::any())->willReturn(2);

        $this->handle(new SendBusinessEventToWebhooksCommand($bulkEvent));

        $log = EventSubscriptionRequestsLimitReachedLog::fromLimit($webhookRequestsLimit);
        $logger->info(json_encode($log->toLog()))->shouldBeCalled();
    }

    public function it_logs_the_time_it_take_to_build_the_api_events(
        $selectActiveWebhooksQuery,
        $webhookUserAuthenticator,
        $client,
        $builder,
        $eventsApiRequestRepository,
        $countHourlyEventsApiRequestQuery,
        $cacheClearer,
        LoggerInterface $logger
    ): void {
        $getTimeIterable = (function () {
            yield 2; // Start - subscription 1
            yield 3; // End
            yield 5; // Start - subscription 2
            yield 8; // End
            yield 13; // Start - (ignored)
        })();

        /** @var callable Mock a function that return the current time in milliseconds. */
        $getTimeCallable = function () use ($getTimeIterable) {
            $time = $getTimeIterable->current();
            $getTimeIterable->next();

            return $time;
        };

        $this->beConstructedWith(
            $selectActiveWebhooksQuery,
            $webhookUserAuthenticator,
            $client,
            $builder,
            $logger,
            $eventsApiRequestRepository,
            $cacheClearer,
            $countHourlyEventsApiRequestQuery,
            'staging.akeneo.com',
            666,
            $getTimeCallable,
        );

        $author = Author::fromNameAndType('julia', Author::TYPE_UI);
        $bulkEvent = new BulkEvent(
            [
                $this->createEvent($author, ['data']),
            ]
        );

        $subscription1 = new ActiveWebhook('ecommerce', 42, 'a_secret', 'http://localhost/');
        $subscription2 = new ActiveWebhook('ecommerce', 42, 'a_secret', 'http://localhost/');

        $countHourlyEventsApiRequestQuery->execute(Argument::any())->willReturn(1);
        $selectActiveWebhooksQuery->execute()->willReturn([$subscription1, $subscription2]);

        $user = new User();
        $user->setUsername('ecommerce_0000');
        $webhookUserAuthenticator->authenticate(42)->willReturn($user);

        $builder
            ->build(Argument::cetera())
            ->willReturn(
                [
                    new WebhookEvent(
                        'product.created',
                        '5d30d0f6-87a6-45ad-ba6b-3a302b0d328c',
                        '2020-01-01T00:00:00+00:00',
                        $author,
                        'staging.akeneo.com',
                        ['data'],
                    ),
                ]
            );

        $eventsApiRequestRepository->upsert(Argument::any(), Argument::any())->shouldBeCalled();

        $client->bulkSend(
            Argument::that(
                function (iterable $iterable) {
                    iterator_to_array($iterable); // Call the iterator to run the code.

                    return true;
                }
            )
        )->shouldBeCalled();

        $command = new SendBusinessEventToWebhooksCommand($bulkEvent);
        $this->handle($command);

        $expectedBuildTime = (3 - 2) + (8 - 5);
        $log = new EventSubscriptionEventBuildLog(2, $bulkEvent, $expectedBuildTime, 2);
        $logger->info(json_encode($log->toLog()))->shouldBeCalled();
    }

    private function createEvent(Author $author, array $data): EventInterface
    {
        $timestamp = 1577836800;
        $uuid = '5d30d0f6-87a6-45ad-ba6b-3a302b0d328c';

        return new class ($author, $data, $timestamp, $uuid) extends Event {
            public function getName(): string
            {
                return 'product.created';
            }
        };
    }
}
