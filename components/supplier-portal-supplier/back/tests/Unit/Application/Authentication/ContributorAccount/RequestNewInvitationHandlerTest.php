<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Test\Unit\Application\Authentication\ContributorAccount;

use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\Exception\ContributorAccountDoesNotExist;
use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\RequestNewInvitation;
use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\RequestNewInvitationHandler;
use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\SendWelcomeEmailHandler;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Write\Model\ContributorAccount;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Write\ValueObject\Email;
use Akeneo\SupplierPortal\Supplier\Infrastructure\Authentication\ContributorAccount\Repository\InMemory\InMemoryRepository;
use PHPUnit\Framework\TestCase;

final class RequestNewInvitationHandlerTest extends TestCase
{
    /** @test */
    public function itRequestsANewInvitation(): void
    {
        $contributorEmail = 'foo@foo.foo';
        $contributorAccount = ContributorAccount::hydrate(
            'b8b13d0b-496b-4a7c-a574-0d522ba90752',
            $contributorEmail,
            '2022-06-28 10:16:44',
            'P@ssw0rd*',
            '1vn466x20fr44wk40w0s88c40c0owwso0sgoksko0kgcggk848',
            '2022-06-28 10:16:44',
            '2022-06-28 10:16:44',
        );

        $contributorAccountRepository = new InMemoryRepository();
        $contributorAccountRepository->save($contributorAccount);
        $oldContributorAccount = $contributorAccountRepository->findByEmail(Email::fromString($contributorEmail));
        $oldContributorAccountAccessToken = $oldContributorAccount->accessToken();
        $oldContributorAccountPassword = $oldContributorAccount->getPassword();

        $mockSendWelcomeEmailHandler = $this->createMock(SendWelcomeEmailHandler::class);
        $mockSendWelcomeEmailHandler
            ->expects($this->once())
            ->method('__invoke')
            ->withAnyParameters()
        ;

        $sut = new RequestNewInvitationHandler($contributorAccountRepository, $mockSendWelcomeEmailHandler);
        ($sut)(new RequestNewInvitation($contributorEmail));
        $newContributorAccount = $contributorAccountRepository->findByEmail(Email::fromString($contributorEmail));

        static::assertNotSame($oldContributorAccountPassword, $newContributorAccount->getPassword());
        static::assertNotSame($oldContributorAccountAccessToken, $newContributorAccount->accessToken());
        static::assertSame(
            (new \DateTimeImmutable())->format('d'),
            (new \DateTimeImmutable($newContributorAccount->accessTokenCreatedAt()))->format('d'),
        );
    }

    /** @test */
    public function itThrowsAnExceptionIfTheContributorAccountDoesNotExist(): void
    {
        $contributorAccountRepository = new InMemoryRepository();
        $mockSendWelcomeEmailHandler = $this->createMock(SendWelcomeEmailHandler::class);
        $sut = new RequestNewInvitationHandler($contributorAccountRepository, $mockSendWelcomeEmailHandler);

        try {
            ($sut)(new RequestNewInvitation('unknown@example.com'));
            static::fail('ContributorAccountDoesNotExist exception should have been thrown.');
        } catch (ContributorAccountDoesNotExist) {
            static::assertTrue(true);
        }
    }
}
