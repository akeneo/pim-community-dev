<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Test\Unit\Application\Authentication\ContributorAccount;

use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\DeleteContributorAccount;
use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\DeleteContributorAccountHandler;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Event\ContributorAccountDeleted;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Write\Model\ContributorAccount as WriteContributorAccount;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Write\ValueObject\Email;
use Akeneo\SupplierPortal\Supplier\Infrastructure\Authentication\ContributorAccount\Repository\InMemory\InMemoryRepository;
use Akeneo\SupplierPortal\Supplier\Infrastructure\StubEventDispatcher;
use PHPUnit\Framework\TestCase;

class DeleteContributorAccountHandlerTest extends TestCase
{
    /** @test */
    public function itDeletesAContributorAccount(): void
    {
        $eventDispatcherStub = new StubEventDispatcher();
        $repository = new InMemoryRepository();
        $sut = new DeleteContributorAccountHandler($repository, $eventDispatcherStub);

        $repository->save(WriteContributorAccount::fromEmail('contributor@example.com'));

        $this->assertInstanceOf(WriteContributorAccount::class, $repository->findByEmail(Email::fromString('contributor@example.com')));

        ($sut)(new DeleteContributorAccount('contributor@example.com'));

        $this->assertNull($repository->findByEmail(Email::fromString('contributor@example.com')));

        $dispatchedEvents = $eventDispatcherStub->getDispatchedEvents();
        $this->assertCount(1, $dispatchedEvents);
        $this->assertInstanceOf(ContributorAccountDeleted::class, $dispatchedEvents[0]);
    }
}
