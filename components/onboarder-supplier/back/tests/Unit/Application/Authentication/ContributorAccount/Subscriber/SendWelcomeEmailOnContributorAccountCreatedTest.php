<?php

namespace Akeneo\SupplierPortal\Test\Unit\Application\Authentication\ContributorAccount\Subscriber;

use Akeneo\SupplierPortal\Application\Authentication\ContributorAccount\SendWelcomeEmail;
use Akeneo\SupplierPortal\Application\Authentication\ContributorAccount\SendWelcomeEmailHandler;
use Akeneo\SupplierPortal\Application\Authentication\ContributorAccount\Subscriber\SendWelcomeEmailOnContributorAccountCreated;
use Akeneo\SupplierPortal\Domain\Authentication\ContributorAccount\Write\Event\ContributorAccountCreated;
use Akeneo\SupplierPortal\Domain\Authentication\ContributorAccount\Write\Model\ContributorAccount;
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
            ->with(new SendWelcomeEmail($contributorAccount->accessToken(), $contributorAccount->email()));

        $sut = new SendWelcomeEmailOnContributorAccountCreated($sendWelcomeEmailHandlerSpy);

        $sut->sendWelcomeEmail($event);
    }
}
