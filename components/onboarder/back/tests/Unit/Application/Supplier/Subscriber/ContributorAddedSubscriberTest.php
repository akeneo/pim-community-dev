<?php

namespace Akeneo\OnboarderSerenity\Test\Unit\Application\Supplier\Subscriber;

use Akeneo\OnboarderSerenity\Application\Supplier\CreateContributorAccount;
use Akeneo\OnboarderSerenity\Application\Supplier\CreateContributorAccountHandler;
use Akeneo\OnboarderSerenity\Application\Supplier\Subscriber\ContributorAddedSubscriber;
use Akeneo\OnboarderSerenity\Domain\Supplier\Write\Event\ContributorAdded;
use Akeneo\OnboarderSerenity\Domain\Supplier\Write\ValueObject\Identifier;
use PHPUnit\Framework\TestCase;

class ContributorAddedSubscriberTest extends TestCase
{
    /** @test */
    public function itSubscribesToContributorAddedEvent(): void
    {
        $this->assertSame(
            [ContributorAdded::class],
            array_keys(ContributorAddedSubscriber::getSubscribedEvents()),
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

        $sut = new ContributorAddedSubscriber($createContributorAccountHandlerSpy);

        $sut->onContributorAdded($contributorAddedEvent);
    }
}
