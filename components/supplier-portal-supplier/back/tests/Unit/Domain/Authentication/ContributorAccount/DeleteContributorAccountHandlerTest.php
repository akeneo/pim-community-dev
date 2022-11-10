<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Test\Unit\Domain\Authentication\ContributorAccount;

use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\DeleteContributorAccountHandler;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Event\ContributorAccountDeleted;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Write\Model\ContributorAccount as WriteContributorAccount;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Write\ValueObject\Email;
use Akeneo\SupplierPortal\Supplier\Infrastructure\Authentication\ContributorAccount\Repository\InMemory\InMemoryRepository;
use Akeneo\SupplierPortal\Supplier\Infrastructure\StubEventDispatcher;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class DeleteContributorAccountHandlerTest extends TestCase
{
    /** @test */
    public function itDeletesAContributorAccount(): void
    {
        $eventDispatcherStub = new StubEventDispatcher();
        $repository = new InMemoryRepository();
        $sut = new DeleteContributorAccountHandler($repository, $eventDispatcherStub, new NullLogger());

        $repository->save(WriteContributorAccount::createdAtFromEmail(
            'contributor@example.com',
            new \DateTimeImmutable(),
        ));

        $this->assertInstanceOf(WriteContributorAccount::class, $repository->findByEmail(Email::fromString('contributor@example.com')));

        ($sut)('contributor@example.com');

        $this->assertNull($repository->findByEmail(Email::fromString('contributor@example.com')));

        $dispatchedEvents = $eventDispatcherStub->getDispatchedEvents();
        $this->assertCount(1, $dispatchedEvents);
        $this->assertInstanceOf(ContributorAccountDeleted::class, $dispatchedEvents[0]);
    }
}
