<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Test\Unit\Application\Authentication\ContributorAccount\Write;

use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\Write\Exception\InvalidPassword;
use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\Exception\UserHasNotConsent;
use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\Write\UpdatePassword;
use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\Write\UpdatePasswordHandler;
use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\Write\Validation\Password;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\HashPassword;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Write\Exception\ContributorAccountDoesNotExist;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Write\Model\ContributorAccount;
use Akeneo\SupplierPortal\Supplier\Infrastructure\Authentication\ContributorAccount\Repository\InMemory\InMemoryRepository;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class UpdatePasswordHandlerTest extends TestCase
{
    /** @test */
    public function itUpdatesTheContributorPassword(): void
    {
        $passwordHasher = $this->createMock(HashPassword::class);
        $violationsSpy = $this->createMock(ConstraintViolationList::class);
        $contributorAccount = ContributorAccount::hydrate(
            'b8b13d0b-496b-4a7c-a574-0d522ba90752',
            'contributor@example.com',
            '2022-06-06 12:52:44',
            'P@ssw0rd*',
            '1vn466x20fr44wk40w0s88c40c0owwso0sgoksko0kgcggk848',
            '2022-06-06 12:52:44',
            '2022-06-06 12:52:44',
            true,
        );
        $contributorAccountRepository = new InMemoryRepository();
        $contributorAccountRepository->save($contributorAccount);

        $contributorAccount->setPassword('P@ssw0rd*foo');

        $updatePassword = new UpdatePassword(
            'b8b13d0b-496b-4a7c-a574-0d522ba90752',
            'P@ssw0rd*foo',
            true,
        );

        $violationsSpy->expects($this->once())->method('count')->willReturn(0);

        $validatorSpy = $this->createMock(ValidatorInterface::class);
        $validatorSpy
            ->expects($this->once())
            ->method('validate')
            ->willReturn($violationsSpy)
        ;

        $sut = new UpdatePasswordHandler($contributorAccountRepository, $validatorSpy, $passwordHasher, new NullLogger());

        $passwordHasher->expects($this->once())->method('__invoke');
        ($sut)($updatePassword);
    }

    /** @test */
    public function itThrowsAnExceptionIfTheUserHasNotConsentToOurTermsAndConditions(): void
    {
        $passwordHasher = $this->createMock(HashPassword::class);
        $validator = $this->createMock(ValidatorInterface::class);
        $contributorAccountRepository = new InMemoryRepository();

        $updatePassword = new UpdatePassword(
            'b8b13d0b-496b-4a7c-a574-0d522ba90752',
            'P@ssw0rd*foo',
            false,
        );

        $sut = new UpdatePasswordHandler($contributorAccountRepository, $validator, $passwordHasher, new NullLogger());

        static::expectException(UserHasNotConsent::class);
        ($sut)($updatePassword);
    }

    /** @test */
    public function itThrowsAnExceptionIfTheContributorAccountCannotBeFound(): void
    {
        $passwordHasher = $this->createMock(HashPassword::class);
        $validator = $this->createMock(ValidatorInterface::class);
        $contributorAccountRepository = new InMemoryRepository();

        $updatePassword = new UpdatePassword(
            'b8b13d0b-496b-4a7c-a574-0d522ba90752',
            'P@ssw0rd*foo',
            true,
        );

        $sut = new UpdatePasswordHandler($contributorAccountRepository, $validator, $passwordHasher, new NullLogger());

        static::expectException(ContributorAccountDoesNotExist::class);
        ($sut)($updatePassword);
    }

    /** @test */
    public function itThrowsAnInvalidPasswordExceptionIfThePasswordDoesNotFulfillTheRequirements(): void
    {
        $passwordHasher = $this->createMock(HashPassword::class);
        $validatorSpy = $this->createMock(ValidatorInterface::class);
        $contributorAccount = ContributorAccount::hydrate(
            'b8b13d0b-496b-4a7c-a574-0d522ba90752',
            'contributor@example.com',
            '2022-06-06 12:52:44',
            'P@ssw0rd*',
            '1vn466x20fr44wk40w0s88c40c0owwso0sgoksko0kgcggk848',
            '2022-06-06 12:52:44',
            '2022-06-06 12:52:44',
            true,
        );
        $contributorAccountRepository = new InMemoryRepository();
        $contributorAccountRepository->save($contributorAccount);

        $contributorAccount->setPassword('P@ssw0rd*foo');

        $updatePassword = new UpdatePassword(
            'b8b13d0b-496b-4a7c-a574-0d522ba90752',
            'foo',
            true,
        );

        $violationsSpy = $this->createMock(ConstraintViolationList::class);
        $violationsSpy->expects($this->once())->method('count')->willReturn(1);

        $validatorSpy
            ->expects($this->once())
            ->method('validate')
            ->with('foo', new Password())
            ->willReturn($violationsSpy)
        ;

        $sut = new UpdatePasswordHandler($contributorAccountRepository, $validatorSpy, $passwordHasher, new NullLogger());

        $this->expectException(InvalidPassword::class);

        ($sut)($updatePassword);
    }
}
