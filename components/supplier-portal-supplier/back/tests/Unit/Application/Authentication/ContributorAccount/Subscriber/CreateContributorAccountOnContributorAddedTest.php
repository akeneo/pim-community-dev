<?php

namespace Akeneo\SupplierPortal\Supplier\Test\Unit\Application\Authentication\ContributorAccount\Subscriber;

use Akeneo\Platform\Bundle\FeatureFlagBundle\Internal\Test\InMemoryFeatureFlags;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Event\ContributorAdded;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\ValueObject\Identifier;
use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\CreateContributorAccount;
use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\CreateContributorAccountHandler;
use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\Subscriber\CreateContributorAccountOnContributorAdded;
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
    public function itCreatesAContributorAccount(): void
    {
        $contributorAddedEvent = new ContributorAdded(
            Identifier::fromString('4ccdd6c6-a631-48fe-967c-269bcf04e8e0'),
            'contrib1@example.com',
        );

        $createContributorAccountHandlerSpy = $this->createMock(CreateContributorAccountHandler::class);
        $createContributorAccountHandlerSpy
            ->expects($this->once())
            ->method('__invoke')
            ->with(new CreateContributorAccount('contrib1@example.com'));


        $featureFlags = $this->createMock(InMemoryFeatureFlags::class);
        $featureFlags
            ->expects($this->once())
            ->method('isEnabled')
            ->with('supplier_portal_contributor_authentication')
            ->willReturn(true)
        ;

        $sut = new CreateContributorAccountOnContributorAdded(
            $createContributorAccountHandlerSpy,
            $featureFlags,
        );

        $sut->contributorAdded($contributorAddedEvent);
    }
}
