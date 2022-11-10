<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Test\Unit\Application\Authentication\ContributorAccount\Write\CreateContributorAccount;

use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\Write\CreateContributorAccount\CreateContributorAccount;
use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\Write\CreateContributorAccount\CreateContributorAccountHandler;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Event\ContributorAccountCreated;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Write\Model\ContributorAccount;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Write\ValueObject\Email;
use Akeneo\SupplierPortal\Supplier\Infrastructure\Authentication\ContributorAccount\Repository\InMemory\InMemoryRepository;
use Akeneo\SupplierPortal\Supplier\Infrastructure\StubEventDispatcher;
use PHPUnit\Framework\TestCase;

class CreateContributorAccountHandlerTest extends TestCase
{
    /** @test */
    public function itCreatesAContributorAccount(): void
    {
        $eventDispatcherStub = new StubEventDispatcher();
        $repository = new InMemoryRepository();
        $sut = new CreateContributorAccountHandler($repository, $eventDispatcherStub);

        ($sut)(new CreateContributorAccount(
            'contributor@example.com',
            new \DateTimeImmutable(),
        ));

        $createdContributorAccount = $repository->findByEmail(Email::fromString('contributor@example.com'));
        $this->assertInstanceOf(ContributorAccount::class, $createdContributorAccount);
        $this->assertSame('contributor@example.com', (string) $createdContributorAccount->email());

        $dispatchedEvents = $eventDispatcherStub->getDispatchedEvents();
        $this->assertCount(1, $dispatchedEvents);
        $this->assertInstanceOf(ContributorAccountCreated::class, $dispatchedEvents[0]);
    }
}
