<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\Write\ResetPassword;

use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Event\PasswordReset;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Write\ContributorAccountRepository;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Write\ValueObject\Email;
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

        $contributorAccount->resetPasswordAt($resetPassword->resetAt);
        $this->contributorAccountRepository->save($contributorAccount);

        $this->eventDispatcher->dispatch(
            new PasswordReset($resetPassword->email, $contributorAccount->accessToken()),
        );
    }
}
