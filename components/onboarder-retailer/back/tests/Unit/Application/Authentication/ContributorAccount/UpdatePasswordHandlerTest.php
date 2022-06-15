<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Retailer\Test\Unit\Application\Authentication\ContributorAccount;

use Akeneo\OnboarderSerenity\Retailer\Application\Authentication\ContributorAccount\Exception\ContributorAccountDoesNotExist;
use Akeneo\OnboarderSerenity\Retailer\Application\Authentication\ContributorAccount\Exception\InvalidPassword;
use Akeneo\OnboarderSerenity\Retailer\Application\Authentication\ContributorAccount\UpdatePassword;
use Akeneo\OnboarderSerenity\Retailer\Application\Authentication\ContributorAccount\UpdatePasswordHandler;
use Akeneo\OnboarderSerenity\Retailer\Application\Authentication\ContributorAccount\Validation\Password;
use Akeneo\OnboarderSerenity\Retailer\Domain\Authentication\ContributorAccount\Write\Model\ContributorAccount;
use Akeneo\OnboarderSerenity\Retailer\Infrastructure\Authentication\ContributorAccount\Repository\InMemory\InMemoryRepository;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class UpdatePasswordHandlerTest extends TestCase
{
    /** @test */
    public function itUpdatesTheContributorPassword(): void
    {
        $passwordHasher = $this->createMock(UserPasswordHasher::class);
        $violationsSpy = $this->createMock(ConstraintViolationList::class);
        $contributorAccount = ContributorAccount::hydrate(
            'b8b13d0b-496b-4a7c-a574-0d522ba90752',
            'contributor@example.com',
            '2022-06-06 12:52:44',
            'P@ssw0rd*',
            '1vn466x20fr44wk40w0s88c40c0owwso0sgoksko0kgcggk848',
            '2022-06-06 12:52:44',
            '2022-06-06 12:52:44',
        );
        $contributorAccountRepository = new InMemoryRepository();
        $contributorAccountRepository->save($contributorAccount);

        $contributorAccount->setPassword('P@ssw0rd*foo');

        $updatePassword = new UpdatePassword(
            'b8b13d0b-496b-4a7c-a574-0d522ba90752',
            'P@ssw0rd*foo',
        );

        $violationsSpy->expects($this->once())->method('count')->willReturn(0);

        $validatorSpy = $this->createMock(ValidatorInterface::class);
        $validatorSpy
            ->expects($this->once())
            ->method('validate')
            ->willReturn($violationsSpy)
        ;

        $sut = new UpdatePasswordHandler($contributorAccountRepository, $passwordHasher, $validatorSpy, new NullLogger());

        try {
            $passwordHasher->expects($this->once())->method('hashPassword');
            ($sut)($updatePassword);
        } catch (ContributorAccountDoesNotExist) {
            static::fail('ContributorAccountDoesNotExist exception should not have been thrown.');
        }
    }

    /** @test */
    public function itThrowsAnExceptionIfTheContributorAccountCannotBeFound(): void
    {
        $passwordHasher = $this->createMock(UserPasswordHasher::class);
        $validator = $this->createMock(ValidatorInterface::class);
        $contributorAccountRepository = new InMemoryRepository();

        $updatePassword = new UpdatePassword(
            'b8b13d0b-496b-4a7c-a574-0d522ba90752',
            'P@ssw0rd*foo',
        );

        $sut = new UpdatePasswordHandler($contributorAccountRepository, $passwordHasher, $validator, new NullLogger());

        try {
            ($sut)($updatePassword);
            static::fail('ContributorAccountDoesNotExist exception should have been thrown.');
        } catch (ContributorAccountDoesNotExist) {
            static::assertTrue(true);
        }
    }

    /** @test */
    public function itThrowsAnInvalidPasswordExceptionIfThePasswordDoesNotFulfillTheRequirements(): void
    {
        $passwordHasher = $this->createMock(UserPasswordHasher::class);
        $validatorSpy = $this->createMock(ValidatorInterface::class);
        $contributorAccount = ContributorAccount::hydrate(
            'b8b13d0b-496b-4a7c-a574-0d522ba90752',
            'contributor@example.com',
            '2022-06-06 12:52:44',
            'P@ssw0rd*',
            '1vn466x20fr44wk40w0s88c40c0owwso0sgoksko0kgcggk848',
            '2022-06-06 12:52:44',
            '2022-06-06 12:52:44',
        );
        $contributorAccountRepository = new InMemoryRepository();
        $contributorAccountRepository->save($contributorAccount);

        $contributorAccount->setPassword('P@ssw0rd*foo');

        $updatePassword = new UpdatePassword(
            'b8b13d0b-496b-4a7c-a574-0d522ba90752',
            'foo',
        );

        $violationsSpy = $this->createMock(ConstraintViolationList::class);
        $violationsSpy->expects($this->once())->method('count')->willReturn(1);

        $validatorSpy
            ->expects($this->once())
            ->method('validate')
            ->with('foo', new Password())
            ->willReturn($violationsSpy)
        ;

        $sut = new UpdatePasswordHandler($contributorAccountRepository, $passwordHasher, $validatorSpy, new NullLogger());

        $this->expectException(InvalidPassword::class);

        ($sut)($updatePassword);
    }
}
