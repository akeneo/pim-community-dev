<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Supplier\Test\Unit\Infrastructure\Authentication\ContributorAccount\Security;

use Akeneo\OnboarderSerenity\Supplier\Infrastructure\Authentication\ContributorAccount\Security\LogoutEventSubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Event\LogoutEvent;

final class LogoutEventSubscriberTest extends TestCase
{
    /** @test */
    public function itDoesNotReturnARedirectResponseWhenTheUserIsLoggingOut(): void
    {
        $subscriber = new LogoutEventSubscriber();
        $event = new LogoutEvent(new Request(), null);

        $this->assertNull($event->getResponse());
        $subscriber->onLogout($event);
        $this->assertEquals(new Response(), $event->getResponse());
    }
}
