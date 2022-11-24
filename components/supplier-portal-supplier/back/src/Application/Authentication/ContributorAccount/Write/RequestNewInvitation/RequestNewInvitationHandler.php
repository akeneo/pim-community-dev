<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\Write\RequestNewInvitation;

use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\SendWelcomeEmail;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Write\ContributorAccountRepository;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Write\Exception\ContributorAccountDoesNotExist;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Write\ValueObject\Email;

final class RequestNewInvitationHandler
{
    public function __construct(
        private ContributorAccountRepository $contributorAccountRepository,
        private SendWelcomeEmail $sendWelcomeEmail,
    ) {
    }

    public function __invoke(RequestNewInvitation $requestNewInvitation): void
    {
        $contributorAccount = $this->contributorAccountRepository->findByEmail(
            Email::fromString($requestNewInvitation->email),
        );

        if (null === $contributorAccount) {
            throw new ContributorAccountDoesNotExist();
        }

        $contributorAccount->renewAccessTokenAt($requestNewInvitation->requestedAt);

        $this->contributorAccountRepository->save($contributorAccount);

        ($this->sendWelcomeEmail)(
            $requestNewInvitation->email,
            $contributorAccount->accessToken(),
        );
    }
}
