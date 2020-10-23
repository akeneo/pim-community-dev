<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Webhook;

use Akeneo\Connectivity\Connection\Application\Webhook\WebhookEventBuilder;
use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Connectivity\Connection\Domain\Webhook\Exception\WebhookEventDataBuilderNotFoundException;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\WebhookEvent;
use Akeneo\Platform\Component\EventQueue\BusinessEvent;
use Akeneo\Platform\Component\EventQueue\BusinessEventInterface;
use Akeneo\Platform\Component\Webhook\EventDataBuilderInterface;
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
        EventDataBuilderInterface $eventDataBuilder1,
        EventDataBuilderInterface $eventDataBuilder2
    ): void {
        $this->beConstructedWith([$eventDataBuilder1, $eventDataBuilder2]);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(WebhookEventBuilder::class);
    }

    public function it_builds_a_webhook_event($eventDataBuilder1, $eventDataBuilder2, UserInterface $user): void
    {
        $user->getUsername()->willReturn('julia');
        $user->getFirstName()->willReturn('Julia');
        $user->getLastName()->willReturn('Doe');
        $user->isApiUser()->willReturn(false);

        $author = Author::fromUser($user->getWrappedObject());
        $businessEvent = $this->createBusinessEvent(
            $author,
            ['data'],
            1599814161,
            'a20832d1-a1e6-4f39-99ea-a1dd859faddb'
        );

        $eventDataBuilder1->supports($businessEvent)->willReturn(false);
        $eventDataBuilder2->supports($businessEvent)->willReturn(true);

        $eventDataBuilder2->build($businessEvent)->willReturn(['data']);

        $this->build($businessEvent, ['pim_source' => 'staging.akeneo.com'])
            ->shouldBeLike(
                new WebhookEvent(
                    'product.created',
                    'a20832d1-a1e6-4f39-99ea-a1dd859faddb',
                    '2020-09-11T08:49:21+00:00',
                    $author,
                    'staging.akeneo.com',
                    ['data']
                )
            );
    }

    public function it_throws_an_error_if_the_business_event_is_not_supported(UserInterface $user): void
    {
        $this->beConstructedWith([]);

        $user->getUsername()->willReturn('julia');
        $user->getFirstName()->willReturn('Julia');
        $user->getLastName()->willReturn('Doe');
        $user->isApiUser()->willReturn(false);

        $author = Author::fromUser($user->getWrappedObject());
        $businessEvent = $this->createBusinessEvent(
            $author,
            ['data'],
            1599814161,
            'a20832d1-a1e6-4f39-99ea-a1dd859faddb'
        );

        $this->shouldThrow(WebhookEventDataBuilderNotFoundException::class)
            ->during('build', [$businessEvent, ['pim_source' => 'staging.akeneo.com']]);
    }

    public function it_throws_an_exception_if_there_is_no_pim_source_in_context(
        $eventDataBuilder1,
        $eventDataBuilder2,
        UserInterface $user
    ): void {
        $user->getUsername()->willReturn('julia');
        $user->getFirstName()->willReturn('Julia');
        $user->getLastName()->willReturn('Doe');
        $user->isApiUser()->willReturn(false);

        $author = Author::fromUser($user->getWrappedObject());
        $businessEvent = $this->createBusinessEvent(
            $author,
            ['data'],
            1599814161,
            'a20832d1-a1e6-4f39-99ea-a1dd859faddb'
        );

        $eventDataBuilder1->supports($businessEvent)->willReturn(false);
        $eventDataBuilder2->supports($businessEvent)->willReturn(true);

        $eventDataBuilder2->build($businessEvent)->willReturn(['data']);

        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('build', [$businessEvent]);
    }

    public function it_throws_an_exception_if_pim_source_is_empty(
        $eventDataBuilder1,
        $eventDataBuilder2,
        UserInterface $user
    ): void {
        $user->getUsername()->willReturn('julia');
        $user->getFirstName()->willReturn('Julia');
        $user->getLastName()->willReturn('Doe');
        $user->isApiUser()->willReturn(false);

        $author = Author::fromUser($user->getWrappedObject());
        $businessEvent = $this->createBusinessEvent(
            $author,
            ['data'],
            1599814161,
            'a20832d1-a1e6-4f39-99ea-a1dd859faddb'
        );

        $eventDataBuilder1->supports($businessEvent)->willReturn(false);
        $eventDataBuilder2->supports($businessEvent)->willReturn(true);

        $eventDataBuilder2->build($businessEvent)->willReturn(['data']);

        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('build', [$businessEvent, ['pim_source' => '']]);
    }

    public function it_throws_an_exception_if_pim_source_is_null(
        $eventDataBuilder1,
        $eventDataBuilder2,
        UserInterface $user
    ): void {
        $user->getUsername()->willReturn('julia');
        $user->getFirstName()->willReturn('Julia');
        $user->getLastName()->willReturn('Doe');
        $user->isApiUser()->willReturn(false);

        $author = Author::fromUser($user->getWrappedObject());
        $businessEvent = $this->createBusinessEvent(
            $author,
            ['data'],
            1599814161,
            'a20832d1-a1e6-4f39-99ea-a1dd859faddb'
        );

        $eventDataBuilder1->supports($businessEvent)->willReturn(false);
        $eventDataBuilder2->supports($businessEvent)->willReturn(true);

        $eventDataBuilder2->build($businessEvent)->willReturn(['data']);

        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('build', [$businessEvent, ['pim_source' => null]]);
    }

    private function createBusinessEvent(
        Author $author,
        array $data,
        int $timestamp,
        string $uuid
    ): BusinessEventInterface {
        return new class ($author, $data, $timestamp, $uuid) extends BusinessEvent {
            public function name(): string
            {
                return 'product.created';
            }
        };
    }
}
