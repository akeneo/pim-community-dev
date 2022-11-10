<?php

namespace Akeneo\SupplierPortal\Supplier\Test\Unit\Application\Authentication\ContributorAccount\Subscriber;

use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\Subscriber\SendWelcomeEmailOnContributorAccountCreated;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Event\ContributorAccountCreated;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\SendWelcomeEmail;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Write\Model\ContributorAccount;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class SendWelcomeEmailOnContributorAccountCreatedTest extends TestCase
{
    /** @test */
    public function itSubscribesToContributorAccountCreatedEvent(): void
    {
        $this->assertSame(
            [ContributorAccountCreated::class],
            array_keys(SendWelcomeEmailOnContributorAccountCreated::getSubscribedEvents()),
        );
    }

    /** @test */
    public function itCallsTheSendWelcomeEmailService(): void
    {
        $contributorAccount = ContributorAccount::createdAtFromEmail(
            'jeanjacques@example.com',
            new \DateTimeImmutable(),
        );
        $event = new ContributorAccountCreated($contributorAccount);

        $sendWelcomeEmailSpy = $this->createMock(SendWelcomeEmail::class);
        $sendWelcomeEmailSpy
            ->expects($this->once())
            ->method('__invoke')
            ->with($contributorAccount->email(), $contributorAccount->accessToken());

        $sut = new SendWelcomeEmailOnContributorAccountCreated($sendWelcomeEmailSpy, new NullLogger());

        $sut->sendWelcomeEmail($event);
    }
}
