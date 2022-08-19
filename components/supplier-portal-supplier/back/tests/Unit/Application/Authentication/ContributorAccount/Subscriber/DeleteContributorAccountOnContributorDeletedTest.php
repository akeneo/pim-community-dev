<?php

namespace Akeneo\SupplierPortal\Supplier\Test\Unit\Application\Authentication\ContributorAccount\Subscriber;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Event\ContributorDeleted;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\ValueObject\Identifier;
use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\DeleteContributorAccount;
use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\DeleteContributorAccountHandler;
use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\Subscriber\DeleteContributorAccountOnContributorDeleted;
use PHPUnit\Framework\TestCase;

class DeleteContributorAccountOnContributorDeletedTest extends TestCase
{
    /** @test */
    public function itSubscribesToContributorDeletedEvent(): void
    {
        $this->assertSame(
            [ContributorDeleted::class],
            array_keys(DeleteContributorAccountOnContributorDeleted::getSubscribedEvents()),
        );
    }

    /** @test */
    public function itDeletedAContributorAccountWhenAContributorIsDeleted(): void
    {
        $contributorAddeDeletedEvent = new ContributorDeleted(
            Identifier::fromString('4ccdd6c6-a631-48fe-967c-269bcf04e8e0'),
            'contrib1@example.com',
        );

        $createContributorAccountHandlerSpy = $this->createMock(DeleteContributorAccountHandler::class);
        $createContributorAccountHandlerSpy
            ->expects($this->once())
            ->method('__invoke')
            ->with(new DeleteContributorAccount('contrib1@example.com'));

        $sut = new DeleteContributorAccountOnContributorDeleted($createContributorAccountHandlerSpy);

        $sut->contributorDeleted($contributorAddeDeletedEvent);
    }
}
