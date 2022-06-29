<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Supplier\Test\Unit\Application\Authentication\ContributorAccount;

use Akeneo\OnboarderSerenity\Supplier\Application\Authentication\ContributorAccount\Exception\ContributorAccountDoesNotExist;
use Akeneo\OnboarderSerenity\Supplier\Application\Authentication\ContributorAccount\ResetPassword;
use Akeneo\OnboarderSerenity\Supplier\Application\Authentication\ContributorAccount\ResetPasswordHandler;
use Akeneo\OnboarderSerenity\Supplier\Domain\Authentication\ContributorAccount\Write\Model\ContributorAccount;
use Akeneo\OnboarderSerenity\Supplier\Domain\Authentication\ContributorAccount\Write\ValueObject\Email;
use Akeneo\OnboarderSerenity\Supplier\Infrastructure\Authentication\ContributorAccount\Repository\InMemory\InMemoryRepository;
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
        );
        $contributorAccountRepository = new InMemoryRepository();
        $contributorAccountRepository->save($contributorAccount);
        $oldPassword = $contributorAccount->getPassword();

        $sut = new ResetPasswordHandler($contributorAccountRepository);
        ($sut)(new ResetPassword($contributorEmail));

        $newContributorAccount = $contributorAccountRepository->findByEmail(Email::fromString($contributorEmail));

        static::assertNotSame($oldPassword, $newContributorAccount->getPassword());
        static::assertNull($newContributorAccount->getPassword());
    }

    /** @test */
    public function itThrowsAnExceptionIfTheContributorAccountDoesNotExist(): void
    {
        $contributorAccountRepository = new InMemoryRepository();
        $sut = new ResetPasswordHandler($contributorAccountRepository);

        try {
            ($sut)(new ResetPassword('test@example.com'));
            static::fail('ContributorAccountDoesNotExist exception should have been thrown.');
        } catch (ContributorAccountDoesNotExist) {
            static::assertTrue(true);
        }
    }
}
