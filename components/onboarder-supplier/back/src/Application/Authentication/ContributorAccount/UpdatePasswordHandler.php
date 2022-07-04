<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Application\Authentication\ContributorAccount;

use Akeneo\SupplierPortal\Application\Authentication\ContributorAccount\Exception\ContributorAccountDoesNotExist;
use Akeneo\SupplierPortal\Application\Authentication\ContributorAccount\Exception\InvalidPassword;
use Akeneo\SupplierPortal\Application\Authentication\ContributorAccount\Validation\Password;
use Akeneo\SupplierPortal\Domain\Authentication\ContributorAccount\HashPassword;
use Akeneo\SupplierPortal\Domain\Authentication\ContributorAccount\Write\ContributorAccountRepository;
use Akeneo\SupplierPortal\Domain\Authentication\ContributorAccount\Write\ValueObject\Identifier;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class UpdatePasswordHandler
{
    public function __construct(
        private ContributorAccountRepository $contributorAccountRepository,
        private ValidatorInterface $validator,
        private HashPassword $hashPassword,
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

        $violations = $this->validator->validate($updatePassword->plainTextPassword, new Password());
        if (0 < $violations->count()) {
            throw new InvalidPassword($violations);
        }

        $hashedPassword = ($this->hashPassword)($contributorAccount->email(), $updatePassword->plainTextPassword);

        $contributorAccount->setPassword($hashedPassword);

        $this->contributorAccountRepository->save($contributorAccount);
    }
}
