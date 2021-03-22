<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\Webhook\Model;

use Akeneo\Connectivity\Connection\Domain\Webhook\Model\WebhookEvent;
use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\EventQueue\Event;
use Akeneo\Platform\Component\EventQueue\EventInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class WebhookEventSpec extends ObjectBehavior
{
    public function let(UserInterface $user): void
    {
        $user->getUsername()->willReturn('julia');
        $user->getFirstName()->willReturn('Julia');
        $user->getLastName()->willReturn('Doe');
        $user->isApiUser()->willReturn(false);

        $author = Author::fromUser($user->getWrappedObject());
        $this->beConstructedWith(
            'product.created',
            '21f7f779-f094-4305-8ee4-65fdddd5a418',
            '2020-01-01T00:00:00+00:00',
            $author,
            'staging.akeneo.com',
            ['data'],
            $this->createEvent($author, ['data'])
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(WebhookEvent::class);
    }

    public function it_returns_an_action(): void
    {
        $this->action()->shouldReturn('product.created');
    }

    public function it_returns_an_event_id(): void
    {
        $this->eventId()->shouldReturn('21f7f779-f094-4305-8ee4-65fdddd5a418');
    }

    public function it_returns_an_event_date_time(): void
    {
        $this->eventDateTime()->shouldReturn('2020-01-01T00:00:00+00:00');
    }

    public function it_returns_an_author_name(): void
    {
        $this->author()->name()->shouldReturn('julia');
    }

    public function it_returns_an_author_type(): void
    {
        $this->author()->type()->shouldReturn('ui');
    }

    public function it_returns_a_pim_source(): void
    {
        $this->pimSource()
            ->shouldReturn('staging.akeneo.com');
    }

    public function it_returns_data(): void
    {
        $this->data()->shouldReturn(['data']);
    }

    public function it_returns_version(UserInterface $user):void
    {
        $user->getUsername()->willReturn('julia');
        $user->getFirstName()->willReturn('Julia');
        $user->getLastName()->willReturn('Doe');
        $user->isApiUser()->willReturn(false);

        $author = Author::fromUser($user->getWrappedObject());
        $this->beConstructedWith(
            'product.created',
            '21f7f779-f094-4305-8ee4-65fdddd5a418',
            '2020-01-01T00:00:00+00:00',
            $author,
            'staging.akeneo.com',
            ['data'],
            $this->createEvent($author, ['data']),
            'version'
        );

        $this->version()
            ->shouldReturn('version');
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
