<?php

namespace Akeneo\OnboarderSerenity\Test\Unit\Application\Authentication\ContributorAccount\Subscriber;

use Akeneo\OnboarderSerenity\Application\Authentication\ContributorAccount\CreateContributorAccount;
use Akeneo\OnboarderSerenity\Application\Authentication\ContributorAccount\CreateContributorAccountHandler;
use Akeneo\OnboarderSerenity\Application\Authentication\ContributorAccount\Subscriber\CreateContributorAccountOnContributorAdded;
use Akeneo\OnboarderSerenity\Domain\Supplier\Write\Event\ContributorAdded;
use Akeneo\OnboarderSerenity\Domain\Supplier\Write\ValueObject\Identifier;
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

        $sut = new CreateContributorAccountOnContributorAdded($createContributorAccountHandlerSpy);

        $sut->contributorAdded($contributorAddedEvent);
    }
}
