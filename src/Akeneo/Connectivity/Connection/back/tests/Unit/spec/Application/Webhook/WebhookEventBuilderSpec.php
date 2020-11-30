<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Webhook;

use Akeneo\Connectivity\Connection\Application\Webhook\WebhookEventBuilder;
use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Connectivity\Connection\Domain\Webhook\Exception\WebhookEventDataBuilderNotFoundException;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\WebhookEvent;
use Akeneo\Platform\Component\EventQueue\Event;
use Akeneo\Platform\Component\EventQueue\EventInterface;
use Akeneo\Platform\Component\Webhook\EventDataBuilderInterface;
use Akeneo\Platform\Component\Webhook\EventDataCollection;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WebhookEventBuilderSpec extends ObjectBehavior
{
    public function let(
        EventDataBuilderInterface $notSupportedEventDataBuilder,
        EventDataBuilderInterface $supportedEventDataBuilder,
        LoggerInterface $logger
    ): void {
        $this->beConstructedWith([$notSupportedEventDataBuilder, $supportedEventDataBuilder], $logger);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(WebhookEventBuilder::class);
    }

    public function it_builds_a_webhook_event(
        EventDataBuilderInterface $notSupportedEventDataBuilder,
        EventDataBuilderInterface $supportedEventDataBuilder,
        UserInterface $user
    ): void {
        $author = Author::fromNameAndType('julia', Author::TYPE_UI);
        $event = $this->createEvent($author, ['data'], 1599814161, 'a20832d1-a1e6-4f39-99ea-a1dd859faddb');

        $collection = new EventDataCollection();
        $collection->setEventData($event, ['data']);

        $notSupportedEventDataBuilder->supports($event)->willReturn(false);
        $supportedEventDataBuilder->supports($event)->willReturn(true);

        $supportedEventDataBuilder->build($event, $user)->willReturn($collection);

        $this->build(
            $event,
            [
                'pim_source' => 'staging.akeneo.com',
                'user' => $user,
                'connection_code' => 'ecommerce',
            ]
        )->shouldBeLike(
            [
                new WebhookEvent(
                    'product.created',
                    'a20832d1-a1e6-4f39-99ea-a1dd859faddb',
                    '2020-09-11T08:49:21+00:00',
                    $author,
                    'staging.akeneo.com',
                    ['data'],
                ),
            ]
        );
    }

    public function it_throws_an_error_if_the_business_event_is_not_supported(
        UserInterface $user,
        LoggerInterface $logger
    ): void {
        $this->beConstructedWith([], $logger);

        $author = Author::fromNameAndType('julia', Author::TYPE_UI);
        $event = $this->createEvent($author, ['data'], 1599814161, 'a20832d1-a1e6-4f39-99ea-a1dd859faddb');

        $this->shouldThrow(WebhookEventDataBuilderNotFoundException::class)->during(
            'build',
            [
                $event,
                [
                    'pim_source' => 'staging.akeneo.com',
                    'user' => $user,
                    'connection_code' => 'ecommerce',
                ],
            ]
        );
    }

    public function it_throws_an_exception_if_there_is_no_pim_source_in_context(UserInterface $user): void
    {
        $author = Author::fromNameAndType('julia', Author::TYPE_UI);
        $event = $this->createEvent($author, ['data'], 1599814161, 'a20832d1-a1e6-4f39-99ea-a1dd859faddb');

        $this->shouldThrow(\InvalidArgumentException::class)->during('build', [$event, ['user' => $user]]);
    }

    public function it_throws_an_exception_if_pim_source_is_null(UserInterface $user): void
    {
        $author = Author::fromNameAndType('julia', Author::TYPE_UI);
        $event = $this->createEvent($author, ['data'], 1599814161, 'a20832d1-a1e6-4f39-99ea-a1dd859faddb');

        $this->shouldThrow(\InvalidArgumentException::class)->during('build', [
            $event,
            ['pim_source' => null, 'user' => $user],
        ]);
    }

    public function it_throws_an_exception_if_there_is_no_user_in_context(): void
    {
        $author = Author::fromNameAndType('julia', Author::TYPE_UI);
        $event = $this->createEvent($author, ['data'], 1599814161, 'a20832d1-a1e6-4f39-99ea-a1dd859faddb');

        $this->shouldThrow(\InvalidArgumentException::class)->during('build', [
            $event,
            ['pim_source' => 'staging.akeneo.com'],
        ]);
    }

    public function it_throws_an_exception_if_user_is_null(): void
    {
        $author = Author::fromNameAndType('julia', Author::TYPE_UI);
        $event = $this->createEvent($author, ['data'], 1599814161, 'a20832d1-a1e6-4f39-99ea-a1dd859faddb');

        $this->shouldThrow(\InvalidArgumentException::class)->during('build', [
            $event,
            ['pim_source' => 'staging.akeneo.com', 'user' => null],
        ]);
    }

    private function createEvent(Author $author, array $data, int $timestamp, string $uuid): EventInterface
    {
        return new class ($author, $data, $timestamp, $uuid) extends Event {
            public function getName(): string
            {
                return 'product.created';
            }
        };
    }
}
