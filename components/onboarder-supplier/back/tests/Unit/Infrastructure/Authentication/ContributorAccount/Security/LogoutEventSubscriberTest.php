<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Test\Unit\Infrastructure\Authentication\ContributorAccount\Security;

use Akeneo\SupplierPortal\Infrastructure\Authentication\ContributorAccount\Security\ContributorAccount;
use Akeneo\SupplierPortal\Infrastructure\Authentication\ContributorAccount\Security\LogoutEventSubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;

final class LogoutEventSubscriberTest extends TestCase
{
    /** @test */
    public function itDoesNotReturnARedirectResponseWhenTheUserIsLoggingOut(): void
    {
        $subscriber = new LogoutEventSubscriber();

        $token = $this->createMock(TokenInterface::class);
        $contributorAccount = new ContributorAccount('burger@example.com', 'mypassword');
        $token->expects($this->once())->method('getUser')->willReturn($contributorAccount);

        $event = new LogoutEvent(new Request(), $token);

        $this->assertNull($event->getResponse());
        $subscriber->onLogout($event);
        $this->assertEquals(new Response(), $event->getResponse());
    }

    /** @test */
    public function itDoesNotSetAResponseIfTheUserIsNotAContributor(): void
    {
        $subscriber = new LogoutEventSubscriber();

        $event = new LogoutEvent(new Request(), null);

        $subscriber->onLogout($event);

        $this->assertNull($event->getResponse());
    }
}
