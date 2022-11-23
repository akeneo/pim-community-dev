<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Test\Unit\Application\Authentication\ContributorAccount\Write\RequestNewInvitation;

use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\Write\RequestNewInvitation\RequestNewInvitation;
use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\Write\RequestNewInvitation\RequestNewInvitationHandler;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\SendWelcomeEmail;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Write\Exception\ContributorAccountDoesNotExist;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Write\Model\ContributorAccount;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Write\ValueObject\Email;
use Akeneo\SupplierPortal\Supplier\Infrastructure\Authentication\ContributorAccount\Repository\InMemory\InMemoryRepository;
use Akeneo\SupplierPortal\Supplier\Test\Unit\Fakes\FrozenClock;
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
            null,
            '1vn466x20fr44wk40w0s88c40c0owwso0sgoksko0kgcggk848',
            '2022-06-28 10:16:44',
            '2022-06-28 10:16:44',
            false,
        );

        $contributorAccountRepository = new InMemoryRepository();
        $contributorAccountRepository->save($contributorAccount);
        $oldContributorAccountAccessToken = $contributorAccount->accessToken();

        $mockSendWelcomeEmail = $this->createMock(SendWelcomeEmail::class);
        $mockSendWelcomeEmail
            ->expects($this->once())
            ->method('__invoke')
            ->with($contributorEmail, $this->isType('string'))
        ;

        $sut = new RequestNewInvitationHandler($contributorAccountRepository, $mockSendWelcomeEmail);
        $requestedAt = (new FrozenClock('2022-09-07 08:54:38'))->now();
        ($sut)(new RequestNewInvitation(
            $contributorEmail,
            $requestedAt,
        ));
        $updatedContributorAccount = $contributorAccountRepository->findByEmail(Email::fromString($contributorEmail));

        static::assertNotSame($oldContributorAccountAccessToken, $updatedContributorAccount->accessToken());
        static::assertSame(
            $requestedAt->format('Y-m-d H:i:s'),
            $updatedContributorAccount->accessTokenCreatedAt(),
        );
    }

    /** @test */
    public function itThrowsAnExceptionIfTheContributorAccountDoesNotExist(): void
    {
        $contributorAccountRepository = new InMemoryRepository();
        $mockSendWelcomeEmail = $this->createMock(SendWelcomeEmail::class);
        $sut = new RequestNewInvitationHandler($contributorAccountRepository, $mockSendWelcomeEmail);

        static::expectException(ContributorAccountDoesNotExist::class);
        ($sut)(new RequestNewInvitation(
            'unknown@example.com',
            (new FrozenClock('2022-09-07 08:54:38'))->now(),
        ));
    }
}
