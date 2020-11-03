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
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\SelectActiveWebhooksQuery;
use Akeneo\Platform\Component\EventQueue\BusinessEvent;
use Akeneo\Platform\Component\EventQueue\BusinessEventInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PhpSpec\ObjectBehavior;
use PHPUnit\Framework\Assert;
use Prophecy\Argument;
use Psr\Log\NullLogger;
use Akeneo\Platform\Component\EventQueue\Author;
use Symfony\Component\Security\Core\User\UserProviderInterface;

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
        UserProviderInterface $userManager
    ): void {
        $this->beConstructedWith(
            $selectActiveWebhooksQuery,
            $webhookUserAuthenticator,
            $client,
            $builder,
            new NullLogger(),
            $userManager,
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
        UserInterface $user
    ): void {

        $user->getUsername()->willReturn('julia');
        $user->getFirstName()->willReturn('Julia');
        $user->getLastName()->willReturn('Doe');
        $user->isApiUser()->willReturn(false);

        $author = Author::fromUser($user->getWrappedObject());
        $businessEvent = $this->createBusinessEvent($author, ['data']);
        $command = new SendBusinessEventToWebhooksCommand($businessEvent);
        $webhook = new ActiveWebhook('ecommerce', 0, 'a_secret', 'http://localhost/');

        $selectActiveWebhooksQuery->execute()->willReturn([$webhook]);

        $webhookUserAuthenticator->authenticate(0)->shouldBeCalled();
        $builder->build($businessEvent, ['pim_source' => 'staging.akeneo.com'])->willReturn(
            new WebhookEvent(
                'product.created',
                '5d30d0f6-87a6-45ad-ba6b-3a302b0d328c',
                '2020-01-01T00:00:00+00:00',
                $author,
                'staging.akeneo.com',
                ['data']
            )
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
                            'action' => 'product.created',
                            'event_id' => '5d30d0f6-87a6-45ad-ba6b-3a302b0d328c',
                            'event_date' => '2020-01-01T00:00:00+00:00',
                            'author' => 'julia',
                            'author_type' => 'ui',
                            'pim_source' => 'staging.akeneo.com',
                            'data' => ['data'],
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
        UserInterface $user,
        $userManager
    ): void {

        $user->getId()->willReturn(0);
        $user->getUsername()->willReturn('julia');
        $user->getFirstName()->willReturn('Julia');
        $user->getLastName()->willReturn('Doe');
        $user->isApiUser()->willReturn(false);

        $author = Author::fromUser($user->getWrappedObject());
        $businessEvent = $this->createBusinessEvent($author, ['data']);
        $command = new SendBusinessEventToWebhooksCommand($businessEvent);

        $selectActiveWebhooksQuery->execute()->willReturn([]);
        $userManager->loadUserByUsername(UserInterface::SYSTEM_USER_NAME)->shouldBeCalled()->willReturn($user);

        $webhookUserAuthenticator->authenticate(0)->shouldBeCalled();
        $builder->build($businessEvent, ['pim_source' => 'staging.akeneo.com'])->willReturn(
            new WebhookEvent(
                'product.created',
                '5d30d0f6-87a6-45ad-ba6b-3a302b0d328c',
                '2020-01-01T00:00:00+00:00',
                $author,
                'staging.akeneo.com',
                ['data']
            )
        );

        $client->bulkFakeSend(
            Argument::that(
                function (iterable $iterable) {
                    $requests = iterator_to_array($iterable);

                    Assert::assertCount(1, $requests);
                    Assert::assertContainsOnlyInstancesOf(WebhookRequest::class, $requests);

                    Assert::assertEquals(SendBusinessEventToWebhooksHandler::FAKE_URL, $requests[0]->url());
                    Assert::assertEquals(SendBusinessEventToWebhooksHandler::FAKE_SECRET, $requests[0]->secret());
                    Assert::assertEquals(
                        [
                            'action' => 'product.created',
                            'event_id' => '5d30d0f6-87a6-45ad-ba6b-3a302b0d328c',
                            'event_date' => '2020-01-01T00:00:00+00:00',
                            'author' => 'julia',
                            'author_type' => 'ui',
                            'pim_source' => 'staging.akeneo.com',
                            'data' => ['data'],
                        ],
                        $requests[0]->content()
                    );

                    return true;
                }
            )
        )->shouldBeCalled();

        $this->handle($command);
    }

    public function it_handle_error_gracefully(
        $selectActiveWebhooksQuery,
        $webhookUserAuthenticator,
        $client,
        $builder,
        UserInterface $user
    ): void {
        $user->getUsername()->willReturn('julia');
        $user->getFirstName()->willReturn('Julia');
        $user->getLastName()->willReturn('Doe');
        $user->isApiUser()->willReturn(false);

        $author = Author::fromUser($user->getWrappedObject());
        $businessEvent = $this->createBusinessEvent($author, ['data']);
        $command = new SendBusinessEventToWebhooksCommand($businessEvent);

        $webhook = new ActiveWebhook('ecommerce', 0, 'a_secret', 'http://localhost/');
        $selectActiveWebhooksQuery->execute()->willReturn([$webhook]);

        $webhookUserAuthenticator->authenticate(0)->shouldBeCalled();
        $builder->build($businessEvent, ['pim_source' => 'staging.akeneo.com'])->willThrow(\Exception::class);

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

    private function createBusinessEvent(Author $author, array $data): BusinessEventInterface
    {
        return new class ($author, $data) extends BusinessEvent {
            public function name(): string
            {
                return 'product.created';
            }
        };
    }
}
