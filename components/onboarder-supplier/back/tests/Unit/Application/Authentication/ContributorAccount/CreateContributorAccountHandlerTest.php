<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Supplier\Test\Unit\Application\Authentication\ContributorAccount;

use Akeneo\OnboarderSerenity\Supplier\Application\Authentication\ContributorAccount\CreateContributorAccount;
use Akeneo\OnboarderSerenity\Supplier\Application\Authentication\ContributorAccount\CreateContributorAccountHandler;
use Akeneo\OnboarderSerenity\Supplier\Domain\Authentication\ContributorAccount\Write\Event\ContributorAccountCreated;
use Akeneo\OnboarderSerenity\Supplier\Domain\Authentication\ContributorAccount\Write\Model\ContributorAccount;
use Akeneo\OnboarderSerenity\Supplier\Domain\Authentication\ContributorAccount\Write\ValueObject\Email;
use Akeneo\OnboarderSerenity\Supplier\Infrastructure\Authentication\ContributorAccount\Repository\InMemory\InMemoryRepository;
use Akeneo\OnboarderSerenity\Supplier\Infrastructure\StubEventDispatcher;
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

        $createdContributorAccount = $repository->findByEmail(Email::fromString('contributor@example.com'));
        $this->assertInstanceOf(ContributorAccount::class, $createdContributorAccount);
        $this->assertSame('contributor@example.com', (string) $createdContributorAccount->email());

        $dispatchedEvents = $eventDispatcherStub->getDispatchedEvents();
        $this->assertCount(1, $dispatchedEvents);
        $this->assertInstanceOf(ContributorAccountCreated::class, $dispatchedEvents[0]);
    }
}
