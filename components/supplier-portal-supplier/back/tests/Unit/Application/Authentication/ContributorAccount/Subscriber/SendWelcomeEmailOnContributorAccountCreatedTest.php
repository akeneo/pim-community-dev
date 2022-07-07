<?php

namespace Akeneo\SupplierPortal\Supplier\Test\Unit\Application\Authentication\ContributorAccount\Subscriber;

use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\SendWelcomeEmailHandler;
use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\Subscriber\SendWelcomeEmailOnContributorAccountCreated;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Event\ContributorAccountCreated;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Write\Model\ContributorAccount;
use PHPUnit\Framework\TestCase;

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
    public function itCallsTheSendWelcomeEmailHandler(): void
    {
        $contributorAccount = ContributorAccount::fromEmail('jeanjacques@example.com');
        $event = new ContributorAccountCreated($contributorAccount);

        $sendWelcomeEmailHandlerSpy = $this->createMock(SendWelcomeEmailHandler::class);
        $sendWelcomeEmailHandlerSpy
            ->expects($this->once())
            ->method('__invoke')
            ->with($contributorAccount->email(), $contributorAccount->accessToken());

        $sut = new SendWelcomeEmailOnContributorAccountCreated($sendWelcomeEmailHandlerSpy);

        $sut->sendWelcomeEmail($event);
    }
}
