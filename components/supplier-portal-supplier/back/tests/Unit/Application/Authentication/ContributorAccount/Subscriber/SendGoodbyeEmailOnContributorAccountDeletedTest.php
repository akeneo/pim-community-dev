<?php

namespace Akeneo\SupplierPortal\Supplier\Test\Unit\Application\Authentication\ContributorAccount\Subscriber;

use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\Subscriber\SendGoodbyeEmailOnContributorAccountDeleted;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Event\ContributorAccountDeleted;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\SendGoodbyeEmail;
use PHPUnit\Framework\TestCase;

class SendGoodbyeEmailOnContributorAccountDeletedTest extends TestCase
{
    /** @test */
    public function itSubscribesToContributorAccountDeletedEvent(): void
    {
        $this->assertSame(
            [ContributorAccountDeleted::class],
            array_keys(SendGoodbyeEmailOnContributorAccountDeleted::getSubscribedEvents()),
        );
    }

    /** @test */
    public function itCallsTheSendGoodbyeEmailService(): void
    {
        $event = new ContributorAccountDeleted('jeanjacques@example.com');

        $sendGoodbyeEmailSpy = $this->createMock(SendGoodbyeEmail::class);
        $sendGoodbyeEmailSpy
            ->expects($this->once())
            ->method('__invoke')
            ->with('jeanjacques@example.com');

        $sut = new SendGoodbyeEmailOnContributorAccountDeleted($sendGoodbyeEmailSpy);

        $sut->sendGoodbyeEmail($event);
    }
}
