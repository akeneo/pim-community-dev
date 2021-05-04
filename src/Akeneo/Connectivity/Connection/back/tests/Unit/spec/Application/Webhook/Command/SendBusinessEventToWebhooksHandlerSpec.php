<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Webhook\Command;

use Akeneo\Connectivity\Connection\Application\Webhook\Command\SendBusinessEventToWebhooksCommand;
use Akeneo\Connectivity\Connection\Application\Webhook\Command\SendBusinessEventToWebhooksHandler;
use Akeneo\Connectivity\Connection\Application\Webhook\Service\CacheClearerInterface;
use Akeneo\Connectivity\Connection\Application\Webhook\Service\EventSubscriptionSkippedOwnEventLogger;
use Akeneo\Connectivity\Connection\Application\Webhook\WebhookEventBuilder;
use Akeneo\Connectivity\Connection\Application\Webhook\WebhookUserAuthenticator;
use Akeneo\Connectivity\Connection\Domain\Webhook\Client\WebhookClient;
use Akeneo\Connectivity\Connection\Domain\Webhook\Client\WebhookRequest;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Read\ActiveWebhook;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\WebhookEvent;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\SelectActiveWebhooksQuery;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Repository\EventsApiDebugRepository;
use Akeneo\Connectivity\Connection\Infrastructure\Persistence\Dbal\Repository\DbalEventsApiRequestCountRepository;
use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\EventQueue\BulkEvent;
use Akeneo\Platform\Component\EventQueue\Event;
use Akeneo\Platform\Component\EventQueue\EventInterface;
use Akeneo\UserManagement\Component\Model\User;
use PhpSpec\ObjectBehavior;
use PHPUnit\Framework\Assert;
use Prophecy\Argument;
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
        LoggerInterface $logger,
        DbalEventsApiRequestCountRepository $eventsApiRequestRepository,
        CacheClearerInterface $cacheClearer,
        EventSubscriptionSkippedOwnEventLogger $eventSubscriptionSkippedOwnEventLogger,
        EventsApiDebugRepository $eventsApiDebugRepository
    ): void {
        $this->beConstructedWith(
            $selectActiveWebhooksQuery,
            $webhookUserAuthenticator,
            $client,
            $builder,
            $logger,
            $eventSubscriptionSkippedOwnEventLogger,
            $eventsApiDebugRepository,
            $eventsApiRequestRepository,
            $cacheClearer,
            'staging.akeneo.com'
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
        $pimEventBulk = new BulkEvent([
            $this->createEvent($author, ['data'])
        ]);
        $command = new SendBusinessEventToWebhooksCommand($pimEventBulk);
        $webhook = new ActiveWebhook('ecommerce', 42, 'a_secret', 'http://localhost/');

        $selectActiveWebhooksQuery->execute()->willReturn([$webhook]);

        $webhookUserAuthenticator->authenticate(42)->willReturn($magentoUser);
        $builder
            ->build(
                $pimEventBulk,
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
                        $this->createEvent($author, ['data'])
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
                                        'event_datetime' => '2020-01-01T00:00:00+00:00',
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
        $pimEventBulk = new BulkEvent([
            $this->createEvent($erpAuthor, ['data'])
        ]);
        $command = new SendBusinessEventToWebhooksCommand($pimEventBulk);
        $erpWebhook = new ActiveWebhook('erp_source', 42, 'a_secret', 'http://localhost/');
        $magentoWebhook = new ActiveWebhook('ecommerce_destination', 12, 'a_secret', 'http://localhost/');

        $selectActiveWebhooksQuery->execute()->willReturn([$erpWebhook, $magentoWebhook]);
        $webhookUserAuthenticator->authenticate(12)->willReturn($magentoUser);
        $webhookUserAuthenticator->authenticate(42)->willReturn($erpUser);

        $builder
            ->build(
                $pimEventBulk,
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
                        $this->createEvent($erpAuthor, ['data'])
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
                                        'event_datetime' => '2020-01-01T00:00:00+00:00',
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
        $cacheClearer,
        $eventsApiRequestRepository
    ): void {
        $user = new User();
        $user->setId(0);
        $user->setUsername('julia');
        $user->setFirstName('Julia');
        $user->setLastName('Doe');

        $author = Author::fromUser($user);
        $pimEventBulk = new BulkEvent([
            $this->createEvent($author, ['data'])
        ]);
        $command = new SendBusinessEventToWebhooksCommand($pimEventBulk);
        $webhook = new ActiveWebhook('ecommerce', 0, 'a_secret', 'http://localhost/');

        $selectActiveWebhooksQuery->execute()->willReturn([$webhook]);
        $webhookUserAuthenticator->authenticate(0)->willReturn($user);
        $builder
            ->build(
                $pimEventBulk,
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

    public function test_it_logs_for_the_events_api_debug_when_an_event_subscription_skipped_its_own_event(
        SelectActiveWebhooksQuery $selectActiveWebhooksQuery,
        WebhookUserAuthenticator $webhookUserAuthenticator,
        EventSubscriptionSkippedOwnEventLogger $eventSubscriptionSkippedOwnEventLogger,
        EventsApiDebugRepository $eventsApiDebugRepository,
        DbalEventsApiRequestCountRepository $eventsApiRequestRepository,
        WebhookClient $client
    ): void {
        $user = new User();
        $user->setId(0);
        $user->setUsername('erp_0000');
        $user->defineAsApiUser();

        $webhook = new ActiveWebhook('erp', $user->getId(), 'a_secret', 'http://localhost/');
        $selectActiveWebhooksQuery->execute()
            ->willReturn([$webhook]);

        $webhookUserAuthenticator->authenticate($user->getId())
            ->willReturn($user);

        $author = Author::fromUser($user);
        $pimEvent = $this->createEvent($author, []);
        $pimEventBulk = new BulkEvent([$pimEvent]);

        $eventSubscriptionSkippedOwnEventLogger->logEventSubscriptionSkippedOwnEvent('erp', $pimEvent)
            ->shouldBeCalled();

        $eventsApiDebugRepository->flush()
            ->shouldBeCalled();

        $eventsApiRequestRepository->upsert(Argument::cetera())
            ->willReturn(0);

        $client->bulkSend(
            Argument::that(
                function (iterable $iterable) {
                    iterator_to_array($iterable); // Call the iterator to run the code.

                    return true;
                }
            )
        )->shouldBeCalled();

        $command = new SendBusinessEventToWebhooksCommand($pimEventBulk);
        $this->handle($command);
    }

    private function createEvent(Author $author, array $data): EventInterface
    {
        $timestamp = 1577836800;
        $uuid = '5d30d0f6-87a6-45ad-ba6b-3a302b0d328c';

        return new class($author, $data, $timestamp, $uuid) extends Event
        {
            public function getName(): string
            {
                return 'product.created';
            }
        };
    }
}
