<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\Product\Infrastructure\Validation;

use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Product\Api\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\Api\Command\UserIntent\UserIntent;
use Akeneo\Pim\Enrichment\Product\Api\Command\UserIntent\ValueUserIntent;
use Akeneo\Pim\Enrichment\Product\Domain\Model\Permission\AccessLevel;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class UserLocaleShouldBeGrantedValidator extends ConstraintValidator
{
    public function __construct(
        private ?VoterInterface $localeVoter,
        private LocaleRepositoryInterface $localeRepository,
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function validate($command, Constraint $constraint): void
    {
        Assert::isInstanceOf($command, UpsertProductCommand::class);

        $user = $this->userRepository->findOneBy(['id' => $command->userId()]);
        if (null === $user) {
            return;
        }

        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());

        /** @var ValueUserIntent $userIntent */
        foreach ($command->userIntents() as $userIntent) {
            if (null !== $userIntent->localeCode()) {
                $locale = $this->localeRepository->findOneByIdentifier($userIntent->localeCode());
                if (null !== $locale) {
                    if (VoterInterface::ACCESS_GRANTED !== $this->localeVoter->vote($token, $locale, [AccessLevel::EDIT_ITEMS])) {
                        $this->context->buildViolation("You don't have access to product data in any activated locale, please contact your administrator")
                            ->addViolation();
                    }
                }
            }
        }
    }
}
