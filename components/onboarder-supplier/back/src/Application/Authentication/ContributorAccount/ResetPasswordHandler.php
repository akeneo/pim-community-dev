<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Application\Authentication\ContributorAccount;

use Akeneo\SupplierPortal\Domain\Authentication\ContributorAccount\Write\ContributorAccountRepository;
use Akeneo\SupplierPortal\Domain\Authentication\ContributorAccount\Write\Event\ResetPasswordRequested;
use Akeneo\SupplierPortal\Domain\Authentication\ContributorAccount\Write\ValueObject\Email;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class ResetPasswordHandler
{
    public function __construct(
        private ContributorAccountRepository $contributorAccountRepository,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function __invoke(ResetPassword $resetPassword): void
    {
        $contributorAccount = $this->contributorAccountRepository->findByEmail(
            Email::fromString($resetPassword->email),
        );

        if (null === $contributorAccount) {
            return;
        }

        $contributorAccount->resetPassword();
        $this->contributorAccountRepository->save($contributorAccount);

        $this->eventDispatcher->dispatch(
            new ResetPasswordRequested($resetPassword->email, $contributorAccount->accessToken()),
        );
    }
}
