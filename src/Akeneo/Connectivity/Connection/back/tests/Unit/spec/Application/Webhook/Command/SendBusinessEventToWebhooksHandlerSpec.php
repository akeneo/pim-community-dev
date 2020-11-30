<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Webhook\Command;

use Akeneo\Connectivity\Connection\Application\Webhook\Command\SendBusinessEventToWebhooksCommand;
use Akeneo\Connectivity\Connection\Application\Webhook\Command\SendBusinessEventToWebhooksHandler;
use Akeneo\Connectivity\Connection\Application\Webhook\WebhookEventBuilder;
use Akeneo\Connectivity\Connection\Application\Webhook\WebhookUserAuthenticator;
use Akeneo\Connectivity\Connection\Domain\Webhook\Client\WebhookClient;
use Akeneo\Connectivity\Connection\Domain\Webhook\Client\WebhookRequest;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Read\ActiveWebhook;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\WebhookEvent;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\GetConnectionUserForFakeSubscription;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\SelectActiveWebhooksQuery;
use Akeneo\Platform\Component\EventQueue\Event;
use Akeneo\Platform\Component\EventQueue\EventInterface;
use Akeneo\UserManagement\Component\Model\User;
use PhpSpec\ObjectBehavior;
use PHPUnit\Framework\Assert;
use Prophecy\Argument;
use Psr\Log\NullLogger;
use Akeneo\Platform\Component\EventQueue\Author;

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
        GetConnectionUserForFakeSubscription $connectionUserForFakeSubscription
    ): void {
        $this->beConstructedWith(
            $selectActiveWebhooksQuery,
            $webhookUserAuthenticator,
            $client,
            $builder,
            new NullLogger(),
            $connectionUserForFakeSubscription,
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
        $builder
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
        $businessEvent = $this->createBusinessEvent($author, ['data']);
        $command = new SendBusinessEventToWebhooksCommand($businessEvent);
        $webhook = new ActiveWebhook('ecommerce', 42, 'a_secret', 'http://localhost/');

        $selectActiveWebhooksQuery->execute()->willReturn([$webhook]);

        $webhookUserAuthenticator->authenticate(42)->willReturn($magentoUser);
        $builder->build(
            $businessEvent,
            [
                'user' => $magentoUser,
                'pim_source' => 'staging.akeneo.com',
                'connection_code' => $webhook->connectionCode(),
            ]
        )->willReturn(
            [
                new WebhookEvent(
                    'product.created',
                    '5d30d0f6-87a6-45ad-ba6b-3a302b0d328c',
                    '2020-01-01T00:00:00+00:00',
                    $author,
                    'staging.akeneo.com',
                    ['data']
                )
            ]
        );

        $client->bulkSend(
            Argument::that(
                function (iterable $iterable) {
                    $requests = iterator_to_array($iterable);

                    Assert::assertCount(1, $requests);
                    Assert::assertContainsOnlyInstancesOf(WebhookRequest::class, $requests);

                    Assert::assertEquals('http://localhost/', $requests[0]->url());
                    Assert::assertEquals('a_secret', $requests[0]->secret());
                    Assert::assertEquals(
                        [
                            [
                                'action' => 'product.created',
                                'event_id' => '5d30d0f6-87a6-45ad-ba6b-3a302b0d328c',
                                'event_date' => '2020-01-01T00:00:00+00:00',
                                'author' => 'julia',
                                'author_type' => 'ui',
                                'pim_source' => 'staging.akeneo.com',
                                'data' => ['data'],
                            ]
                        ],
                        $requests[0]->content()
                    );

                    return true;
                }
            )
        )->shouldBeCalled();

        $this->handle($command);
    }

    public function it_sends_fake_message_if_there_is_no_webhook(
        $selectActiveWebhooksQuery,
        $webhookUserAuthenticator,
        $client,
        $builder,
        $connectionUserForFakeSubscription
    ): void {
        $julia = new User();
        $julia->setId(1234);
        $julia->setUsername('julia');
        $julia->setFirstName('Julia');
        $julia->setLastName('Doe');
        $magentoUser = new User();
        $magentoUser->setId(42);
        $magentoUser->setUsername('magento_452');
        $magentoUser->setFirstName('magento_452');
        $magentoUser->setLastName('magento_452');
        $magentoUser->defineAsApiUser();

        $author = Author::fromUser($julia);
        $businessEvent = $this->createBusinessEvent($author, ['data']);
        $command = new SendBusinessEventToWebhooksCommand($businessEvent);

        $selectActiveWebhooksQuery->execute()->willReturn([]);
        $connectionUserForFakeSubscription->execute()->willReturn(1234);

        $webhookUserAuthenticator->authenticate(1234)->willReturn($magentoUser);
        $builder->build(
            $businessEvent,
            [
                'pim_source' => 'staging.akeneo.com',
                'user' => $magentoUser,
                'connection_code' => SendBusinessEventToWebhooksHandler::FAKE_CONNECTION_CODE,
            ]
        )->willReturn(
            [
                new WebhookEvent(
                    'product.created',
                    '5d30d0f6-87a6-45ad-ba6b-3a302b0d328c',
                    '2020-01-01T00:00:00+00:00',
                    $author,
                    'staging.akeneo.com',
                    ['data']
                )
            ]
        );

        $client->bulkFakeSend(
            Argument::that(
                function (iterable $iterable) {
                    $requests = iterator_to_array($iterable);

                    Assert::assertCount(3, $requests);

                    Assert::assertContainsOnlyInstancesOf(WebhookRequest::class, $requests);
                    Assert::assertEquals(SendBusinessEventToWebhooksHandler::FAKE_URL, $requests[0]->url());
                    Assert::assertEquals(SendBusinessEventToWebhooksHandler::FAKE_SECRET, $requests[0]->secret());
                    Assert::assertEquals(
                        [
                            [
                                'action' => 'product.created',
                                'event_id' => '5d30d0f6-87a6-45ad-ba6b-3a302b0d328c',
                                'event_date' => '2020-01-01T00:00:00+00:00',
                                'author' => 'julia',
                                'author_type' => 'ui',
                                'pim_source' => 'staging.akeneo.com',
                                'data' => ['data'],
                            ]
                        ],
                        $requests[0]->content()
                    );
                    Assert::assertContainsOnlyInstancesOf(WebhookRequest::class, $requests);
                    Assert::assertEquals(SendBusinessEventToWebhooksHandler::FAKE_URL, $requests[1]->url());
                    Assert::assertEquals(SendBusinessEventToWebhooksHandler::FAKE_SECRET, $requests[1]->secret());
                    Assert::assertEquals(
                        [
                            [
                                'action' => 'product.created',
                                'event_id' => '5d30d0f6-87a6-45ad-ba6b-3a302b0d328c',
                                'event_date' => '2020-01-01T00:00:00+00:00',
                                'author' => 'julia',
                                'author_type' => 'ui',
                                'pim_source' => 'staging.akeneo.com',
                                'data' => ['data'],
                            ]
                        ],
                        $requests[1]->content()
                    );
                    Assert::assertContainsOnlyInstancesOf(WebhookRequest::class, $requests);
                    Assert::assertEquals(SendBusinessEventToWebhooksHandler::FAKE_URL, $requests[2]->url());
                    Assert::assertEquals(SendBusinessEventToWebhooksHandler::FAKE_SECRET, $requests[2]->secret());
                    Assert::assertEquals(
                        [
                            [
                                'action' => 'product.created',
                                'event_id' => '5d30d0f6-87a6-45ad-ba6b-3a302b0d328c',
                                'event_date' => '2020-01-01T00:00:00+00:00',
                                'author' => 'julia',
                                'author_type' => 'ui',
                                'pim_source' => 'staging.akeneo.com',
                                'data' => ['data'],
                            ]
                        ],
                        $requests[2]->content()
                    );

                    return true;
                }
            )
        )->shouldBeCalled();

        $this->handle($command);
    }

    public function it_does_not_send_the_message_if_the_webhook_is_the_author_of_the_business_event(
        $selectActiveWebhooksQuery,
        $webhookUserAuthenticator,
        $client,
        $builder
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
        $businessEvent = $this->createBusinessEvent($erpAuthor, ['data']);
        $command = new SendBusinessEventToWebhooksCommand($businessEvent);
        $erpWebhook = new ActiveWebhook('erp_source', 42, 'a_secret', 'http://localhost/');
        $magentoWebhook = new ActiveWebhook('ecommerce_destination', 12, 'a_secret', 'http://localhost/');

        $selectActiveWebhooksQuery->execute()->willReturn([$erpWebhook, $magentoWebhook]);
        $webhookUserAuthenticator->authenticate(12)->willReturn($magentoUser);
        $webhookUserAuthenticator->authenticate(42)->willReturn($erpUser);

        $builder->build(
            $businessEvent,
            [
                'pim_source' => 'staging.akeneo.com',
                'user' => $magentoUser,
                'connection_code' => $magentoWebhook->connectionCode(),
            ]
        )->willReturn(
            [
                new WebhookEvent(
                    'product.created',
                    '5d30d0f6-87a6-45ad-ba6b-3a302b0d328c',
                    '2020-01-01T00:00:00+00:00',
                    $erpAuthor,
                    'staging.akeneo.com',
                    ['data']
                )
            ]
        );

        $client->bulkSend(
            Argument::that(
                function (iterable $iterable) {
                    $requests = iterator_to_array($iterable);

                    Assert::assertCount(1, $requests);
                    Assert::assertContainsOnlyInstancesOf(WebhookRequest::class, $requests);

                    Assert::assertEquals('http://localhost/', $requests[0]->url(), 'Url is not equal');
                    Assert::assertEquals('a_secret', $requests[0]->secret(), 'Secret is not equal');
                    Assert::assertEquals(
                        [
                            [
                                'action' => 'product.created',
                                'event_id' => '5d30d0f6-87a6-45ad-ba6b-3a302b0d328c',
                                'event_date' => '2020-01-01T00:00:00+00:00',
                                'author' => 'erp_452',
                                'author_type' => 'api',
                                'pim_source' => 'staging.akeneo.com',
                                'data' => ['data'],
                            ]
                        ],
                        $requests[0]->content(),
                        'Content is not equal'
                    );

                    return true;
                }
            )
        )->shouldBeCalled();

        $this->handle($command);
    }

    public function it_handles_error_gracefully(
        $selectActiveWebhooksQuery,
        $webhookUserAuthenticator,
        $client,
        $builder
    ): void {
        $user = new User();
        $user->setId(0);
        $user->setUsername('julia');
        $user->setFirstName('Julia');
        $user->setLastName('Doe');

        $author = Author::fromUser($user);
        $businessEvent = $this->createBusinessEvent($author, ['data']);
        $command = new SendBusinessEventToWebhooksCommand($businessEvent);

        $webhook = new ActiveWebhook('ecommerce', 0, 'a_secret', 'http://localhost/');
        $selectActiveWebhooksQuery->execute()->willReturn([$webhook]);

        $webhookUserAuthenticator->authenticate(0)->willReturn($user);
        $builder->build(
            $businessEvent,
            [
                'pim_source' => 'staging.akeneo.com',
                'user' => $user,
                'connection_code' => $webhook->connectionCode(),
            ]
        )->willThrow(
            \Exception::class
        );

        $client->bulkSend(
            Argument::that(
                function (iterable $iterable) {
                    $requests = iterator_to_array($iterable);

                    Assert::assertCount(0, $requests);

                    return true;
                }
            )
        )->shouldBeCalled();

        $this->handle($command);
    }

    private function createBusinessEvent(Author $author, array $data): EventInterface
    {
        return new class ($author, $data) extends Event
        {
            public function getName(): string
            {
                return 'product.created';
            }
        };
    }
}
