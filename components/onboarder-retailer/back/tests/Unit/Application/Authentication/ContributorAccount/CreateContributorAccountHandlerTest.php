<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Retailer\Test\Unit\Application\Authentication\ContributorAccount;

use Akeneo\OnboarderSerenity\Retailer\Application\Authentication\ContributorAccount\CreateContributorAccount;
use Akeneo\OnboarderSerenity\Retailer\Application\Authentication\ContributorAccount\CreateContributorAccountHandler;
use Akeneo\OnboarderSerenity\Retailer\Domain\Authentication\ContributorAccount\Write\Event\ContributorAccountCreated;
use Akeneo\OnboarderSerenity\Retailer\Domain\Authentication\ContributorAccount\Write\Model\ContributorAccount;
use Akeneo\OnboarderSerenity\Retailer\Infrastructure\Authentication\ContributorAccount\Repository\InMemory\InMemoryRepository;
use Akeneo\OnboarderSerenity\Retailer\Infrastructure\StubEventDispatcher;
use PHPUnit\Framework\TestCase;

class CreateContributorAccountHandlerTest extends TestCase
{
    /** @test */
    public function itCreatesAContributorAccount(): void
    {
        $eventDispatcherStub = new StubEventDispatcher();
        $repository = new InMemoryRepository();
        $sut = new CreateContributorAccountHandler($repository, $eventDispatcherStub);

        ($sut)(new CreateContributorAccount('contributor@example.com'));

        $createdContributorAccount = $repository->findByEmail('contributor@example.com');
        $this->assertInstanceOf(ContributorAccount::class, $createdContributorAccount);
        $this->assertSame('contributor@example.com', (string) $createdContributorAccount->email());

        $dispatchedEvents = $eventDispatcherStub->getDispatchedEvents();
        $this->assertCount(1, $dispatchedEvents);
        $this->assertInstanceOf(ContributorAccountCreated::class, $dispatchedEvents[0]);
    }
}
