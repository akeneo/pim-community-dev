<?php

namespace Akeneo\SupplierPortal\Supplier\Test\Unit\Application\Authentication\ContributorAccount\Subscriber;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Event\ContributorAdded;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Model\Supplier\Identifier;
use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\Subscriber\CreateContributorAccountOnContributorAdded;
use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\Write\CreateContributorAccount\CreateContributorAccount;
use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\Write\CreateContributorAccount\CreateContributorAccountHandler;
use Akeneo\SupplierPortal\Supplier\Test\Unit\Fakes\FrozenClock;
use PHPUnit\Framework\TestCase;

class CreateContributorAccountOnContributorAddedTest extends TestCase
{
    /** @test */
    public function itSubscribesToContributorAddedEvent(): void
    {
        $this->assertSame(
            [ContributorAdded::class],
            array_keys(CreateContributorAccountOnContributorAdded::getSubscribedEvents()),
        );
    }

    /** @test */
    public function itCreatesAContributorAccountWhenSupplierPortalIsActivated(): void
    {
        $contributorCreatedAt = (new FrozenClock('2022-09-07 08:54:38'))->now();
        $contributorAddedEvent = new ContributorAdded(
            Identifier::fromString('4ccdd6c6-a631-48fe-967c-269bcf04e8e0'),
            'contrib1@example.com',
            'los_pollos_hermanos',
            $contributorCreatedAt,
        );

        $createContributorAccountHandlerSpy = $this->createMock(CreateContributorAccountHandler::class);
        $createContributorAccountHandlerSpy
            ->expects($this->once())
            ->method('__invoke')
            ->with(new CreateContributorAccount('contrib1@example.com', $contributorCreatedAt));

        $sut = new CreateContributorAccountOnContributorAdded($createContributorAccountHandlerSpy);

        $sut->contributorAdded($contributorAddedEvent);
    }
}
