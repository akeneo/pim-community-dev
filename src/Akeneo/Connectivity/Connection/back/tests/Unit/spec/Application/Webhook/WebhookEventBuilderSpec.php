<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Webhook;

use Akeneo\Connectivity\Connection\Application\Webhook\Service\ApiEventBuildErrorLogger;
use Akeneo\Connectivity\Connection\Application\Webhook\Service\Logger\EventDataBuildErrorLogger;
use Akeneo\Connectivity\Connection\Application\Webhook\WebhookEventBuilder;
use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Connectivity\Connection\Domain\Webhook\Exception\WebhookEventDataBuilderNotFoundException;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\WebhookEvent;
use Akeneo\Platform\Component\EventQueue\BulkEvent;
use Akeneo\Platform\Component\EventQueue\Event;
use Akeneo\Platform\Component\EventQueue\EventInterface;
use Akeneo\Platform\Component\Webhook\EventDataBuilderInterface;
use Akeneo\Platform\Component\Webhook\EventDataCollection;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PhpSpec\ObjectBehavior;

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
        EventDataBuildErrorLogger $eventDataBuildErrorLogger,
        ApiEventBuildErrorLogger $apiEventBuildErrorLogger
    ): void {
        $this->beConstructedWith(
            [$notSupportedEventDataBuilder, $supportedEventDataBuilder],
            $eventDataBuildErrorLogger,
            $apiEventBuildErrorLogger
        );
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
        $pimEvent = $this->createEvent($author, ['data'], 1599814161, 'a20832d1-a1e6-4f39-99ea-a1dd859faddb');
        $pimEventBulk = new BulkEvent([$pimEvent]);

        $collection = new EventDataCollection();
        $collection->setEventData($pimEvent, ['data']);

        $notSupportedEventDataBuilder->supports($pimEventBulk)->willReturn(false);
        $supportedEventDataBuilder->supports($pimEventBulk)->willReturn(true);

        $supportedEventDataBuilder->build($pimEventBulk, $user)->willReturn($collection);

        $this->build(
            $pimEventBulk,
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
                    $this->createEvent($author, ['data'], 1599814161, 'a20832d1-a1e6-4f39-99ea-a1dd859faddb')
                ),
            ]
        );
    }

    public function it_does_not_build_a_webhook_event_when_an_error_has_occured(
        EventDataBuilderInterface $notSupportedEventDataBuilder,
        EventDataBuilderInterface $supportedEventDataBuilder,
        UserInterface $user,
        EventDataBuildErrorLogger $eventDataBuildErrorLogger
    ): void {
        $user->getId()->willReturn(1);
        $author = Author::fromNameAndType('julia', Author::TYPE_UI);
        $pimEvent = $this->createEvent($author, ['data'], 1599814161, 'a20832d1-a1e6-4f39-99ea-a1dd859faddb');
        $pimEventBulk = new BulkEvent([$pimEvent]);

        $collection = new EventDataCollection();
        $collection->setEventDataError($pimEvent, new \Exception());

        $notSupportedEventDataBuilder->supports($pimEventBulk)->willReturn(false);
        $supportedEventDataBuilder->supports($pimEventBulk)->willReturn(true);

        $supportedEventDataBuilder->build($pimEventBulk, $user)->willReturn($collection);

        $eventDataBuildErrorLogger->log(
            '',
            'ecommerce',
            1,
            $pimEvent
        )->shouldBeCalled();

        $this->build(
            $pimEventBulk,
            [
                'pim_source' => 'staging.akeneo.com',
                'user' => $user,
                'connection_code' => 'ecommerce',
            ]
        )->shouldBeLike(
            [
            ]
        );
    }

    public function it_log_when_a_resource_is_not_found(
        EventDataBuilderInterface $notSupportedEventDataBuilder,
        EventDataBuilderInterface $supportedEventDataBuilder,
        UserInterface $user,
        ApiEventBuildErrorLogger $apiEventBuildErrorLogger
    ): void {
        $user->getId()->willReturn(1);
        $author = Author::fromNameAndType('julia', Author::TYPE_UI);
        $pimEvent = $this->createEvent($author, ['data'], 1599814161, 'a20832d1-a1e6-4f39-99ea-a1dd859faddb');
        $pimEventBulk = new BulkEvent([$pimEvent]);

        $collection = new EventDataCollection();
        $collection->setEventDataError($pimEvent, new \Exception());

        $notSupportedEventDataBuilder->supports($pimEventBulk)->willReturn(false);
        $supportedEventDataBuilder->supports($pimEventBulk)->willReturn(true);

        $supportedEventDataBuilder->build($pimEventBulk, $user)->willReturn($collection);

        $apiEventBuildErrorLogger->logResourceNotFoundOrAccessDenied(
            'ecommerce',
            $pimEvent
        )->shouldBeCalled();

        $this->build(
            $pimEventBulk,
            [
                'pim_source' => 'staging.akeneo.com',
                'user' => $user,
                'connection_code' => 'ecommerce',
            ]
        );
    }

    public function it_throws_an_error_if_the_business_event_is_not_supported(
        UserInterface $user,
        EventDataBuildErrorLogger $eventDataBuildErrorLogger,
        ApiEventBuildErrorLogger $apiEventBuildErrorLogger
    ): void {
        $this->beConstructedWith(
            [],
            $eventDataBuildErrorLogger,
            $apiEventBuildErrorLogger
        );

        $author = Author::fromNameAndType('julia', Author::TYPE_UI);
        $pimEvent = $this->createEvent($author, ['data'], 1599814161, 'a20832d1-a1e6-4f39-99ea-a1dd859faddb');
        $pimEventBulk = new BulkEvent([$pimEvent]);

        $this->shouldThrow(WebhookEventDataBuilderNotFoundException::class)->during(
            'build',
            [
                $pimEventBulk,
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
        $pimEvent = $this->createEvent($author, ['data'], 1599814161, 'a20832d1-a1e6-4f39-99ea-a1dd859faddb');
        $pimEventBulk = new BulkEvent([$pimEvent]);

        $expectedException = new \InvalidArgumentException('The required option "pim_source" is missing.');

        $this->shouldThrow($expectedException)->during('build', [
            $pimEventBulk,
            [
                'user' => $user,
                'connection_code' => 'ecommerce',
            ]
        ]);
    }

    public function it_throws_an_exception_if_pim_source_is_null(UserInterface $user): void
    {
        $author = Author::fromNameAndType('julia', Author::TYPE_UI);
        $pimEvent = $this->createEvent($author, ['data'], 1599814161, 'a20832d1-a1e6-4f39-99ea-a1dd859faddb');
        $pimEventBulk = new BulkEvent([$pimEvent]);

        $this->shouldThrow(\InvalidArgumentException::class)->during('build', [
            $pimEventBulk,
            [
                'user' => $user,
                'pim_source' => null,
                'connection_code' => 'ecommerce',
            ],
        ]);
    }

    public function it_throws_an_exception_if_there_is_no_user_in_context(): void
    {
        $author = Author::fromNameAndType('julia', Author::TYPE_UI);
        $pimEvent = $this->createEvent($author, ['data'], 1599814161, 'a20832d1-a1e6-4f39-99ea-a1dd859faddb');
        $pimEventBulk = new BulkEvent([$pimEvent]);

        $expectedException = new \InvalidArgumentException('The required option "pim_source" is missing.');

        $this->shouldThrow(\InvalidArgumentException::class)->during('build', [
            $pimEventBulk,
            [
                'pim_source' => 'staging.akeneo.com',
                'connection_code' => 'ecommerce',
            ],
        ]);
    }

    public function it_throws_an_exception_if_user_is_null(): void
    {
        $author = Author::fromNameAndType('julia', Author::TYPE_UI);
        $pimEvent = $this->createEvent($author, ['data'], 1599814161, 'a20832d1-a1e6-4f39-99ea-a1dd859faddb');
        $pimEventBulk = new BulkEvent([$pimEvent]);

        $this->shouldThrow(\InvalidArgumentException::class)->during('build', [
            $pimEventBulk,
            [
                'user' => null,
                'pim_source' => 'staging.akeneo.com',
                'connection_code' => 'ecommerce',
            ],
        ]);
    }

    public function it_throws_an_exception_if_there_is_no_connection_code_in_context(UserInterface $user): void
    {
        $author = Author::fromNameAndType('julia', Author::TYPE_UI);
        $pimEvent = $this->createEvent($author, ['data'], 1599814161, 'a20832d1-a1e6-4f39-99ea-a1dd859faddb');
        $pimEventBulk = new BulkEvent([$pimEvent]);

        $this->shouldThrow(\InvalidArgumentException::class)->during('build', [
            $pimEventBulk,
            [
                'user' => $user,
                'pim_source' => 'staging.akeneo.com',
            ],
        ]);
    }

    public function it_throws_an_exception_if_connection_code_is_null(UserInterface $user): void
    {
        $author = Author::fromNameAndType('julia', Author::TYPE_UI);
        $pimEvent = $this->createEvent($author, ['data'], 1599814161, 'a20832d1-a1e6-4f39-99ea-a1dd859faddb');
        $pimEventBulk = new BulkEvent([$pimEvent]);

        $this->shouldThrow(\InvalidArgumentException::class)->during('build', [
            $pimEventBulk,
            [
                'user' => $user,
                'pim_source' => 'staging.akeneo.com',
                'connection_code' => null,
            ],
        ]);
    }

    private function createEvent(Author $author, array $data, int $timestamp, string $uuid): EventInterface
    {
        return new class($author, $data, $timestamp, $uuid) extends Event
        {
            public function getName(): string
            {
                return 'product.created';
            }
        };
    }
}
