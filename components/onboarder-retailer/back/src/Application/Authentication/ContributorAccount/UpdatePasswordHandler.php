<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Retailer\Application\Authentication\ContributorAccount;

use Akeneo\OnboarderSerenity\Retailer\Application\Authentication\ContributorAccount\Exception\ContributorAccountDoesNotExist;
use Akeneo\OnboarderSerenity\Retailer\Domain\Authentication\ContributorAccount\Write\ContributorAccountRepository;
use Akeneo\OnboarderSerenity\Retailer\Domain\Authentication\ContributorAccount\Write\ValueObject\Identifier;
use Psr\Log\LoggerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UpdatePasswordHandler
{
    public function __construct(
        private ContributorAccountRepository $contributorAccountRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(UpdatePassword $updatePassword): void
    {
        $contributorAccount = $this->contributorAccountRepository->find(
            Identifier::fromString($updatePassword->contributorAccountIdentifier),
        );

        if (null === $contributorAccount) {
            $this->logger->info(
                'Attempt to update the password of a contributor that does not exist.',
                [
                    'data' => [
                        'contributorAccountIdentifier' => $updatePassword->contributorAccountIdentifier,
                    ],
                ],
            );
            throw new ContributorAccountDoesNotExist();
        }

        $hashedPassword = $this->passwordHasher->hashPassword(
            $contributorAccount,
            $updatePassword->plainTextPassword,
        );
        $contributorAccount->setPassword($hashedPassword);

        $this->contributorAccountRepository->save($contributorAccount);
    }
}
