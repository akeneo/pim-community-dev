<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Supplier\Application\Authentication\ContributorAccount;

use Akeneo\OnboarderSerenity\Supplier\Application\Authentication\ContributorAccount\Exception\ContributorAccountDoesNotExist;
use Akeneo\OnboarderSerenity\Supplier\Domain\Authentication\ContributorAccount\Write\ContributorAccountRepository;
use Akeneo\OnboarderSerenity\Supplier\Domain\Authentication\ContributorAccount\Write\Model\ContributorAccount;
use Akeneo\OnboarderSerenity\Supplier\Domain\Authentication\ContributorAccount\Write\ValueObject\AccessToken;
use Akeneo\OnboarderSerenity\Supplier\Domain\Authentication\ContributorAccount\Write\ValueObject\Email;

final class RequestNewInvitationHandler
{
    public function __construct(
        private ContributorAccountRepository $contributorAccountRepository,
        private SendWelcomeEmailHandler $sendWelcomeEmailHandler,
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

        $newAccessToken = (string) AccessToken::generate();

        $contributorAccount = ContributorAccount::hydrate(
            $contributorAccount->identifier(),
            $contributorAccount->email(),
            $contributorAccount->createdAt(),
            null,
            $newAccessToken,
            (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            null,
        );

        $this->contributorAccountRepository->save($contributorAccount);

        ($this->sendWelcomeEmailHandler)(
            new SendWelcomeEmail(
                $newAccessToken,
                $requestNewInvitation->email,
            )
        );
    }
}
