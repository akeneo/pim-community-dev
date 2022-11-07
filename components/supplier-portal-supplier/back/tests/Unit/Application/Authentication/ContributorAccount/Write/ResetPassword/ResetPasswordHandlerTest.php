<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Test\Unit\Application\Authentication\ContributorAccount\Write\ResetPassword;

use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\Write\ResetPassword\ResetPassword;
use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\Write\ResetPassword\ResetPasswordHandler;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Event\PasswordReset;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Write\Model\ContributorAccount;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Write\ValueObject\Email;
use Akeneo\SupplierPortal\Supplier\Infrastructure\Authentication\ContributorAccount\Repository\InMemory\InMemoryRepository;
use Akeneo\SupplierPortal\Supplier\Infrastructure\StubEventDispatcher;
use PHPUnit\Framework\TestCase;

final class ResetPasswordHandlerTest extends TestCase
{
    /** @test */
    public function itResetsTheContributorAccountPassword(): void
    {
        $contributorEmail = 'foo@foo.foo';
        $contributorAccount = ContributorAccount::hydrate(
            'b8b13d0b-496b-4a7c-a574-0d522ba90752',
            $contributorEmail,
            '2022-06-28 10:16:44',
            'oldP@ssw0rd*',
            '1vn466x20fr44wk40w0s88c40c0owwso0sgoksko0kgcggk848',
            '2022-06-28 10:16:44',
            '2022-06-28 10:16:44',
            true,
        );
        $contributorAccountRepository = new InMemoryRepository();
        $contributorAccountRepository->save($contributorAccount);
        $eventDispatcherStub = new StubEventDispatcher();
        $oldPassword = $contributorAccount->getPassword();

        $sut = new ResetPasswordHandler($contributorAccountRepository, $eventDispatcherStub);
        ($sut)(new ResetPassword($contributorEmail));

        $newContributorAccount = $contributorAccountRepository->findByEmail(Email::fromString($contributorEmail));

        $dispatchedEvents = $eventDispatcherStub->getDispatchedEvents();
        static::assertCount(1, $dispatchedEvents);
        static::assertInstanceOf(PasswordReset::class, $dispatchedEvents[0]);
        static::assertNotSame($oldPassword, $newContributorAccount->getPassword());
        static::assertNull($newContributorAccount->getPassword());
    }

    /** @test */
    public function itDoesNotResetTheContributorAccountPasswordIfItDoesNotExist(): void
    {
        $contributorAccountRepository = $this->createMock(InMemoryRepository::class);
        $eventDispatcherStub = new StubEventDispatcher();
        $dispatchedEvents = $eventDispatcherStub->getDispatchedEvents();
        $sut = new ResetPasswordHandler($contributorAccountRepository, $eventDispatcherStub);

        $contributorAccountRepository->expects(self::never())->method('save')->withAnyParameters();

        ($sut)(new ResetPassword('test@example.com'));

        static::assertCount(0, $dispatchedEvents);
    }
}
