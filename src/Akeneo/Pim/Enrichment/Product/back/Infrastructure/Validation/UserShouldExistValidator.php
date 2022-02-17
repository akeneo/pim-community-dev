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

use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class UserShouldExistValidator extends ConstraintValidator
{
    public function __construct(private UserRepositoryInterface $userRepository)
    {
    }

    public function validate($userId, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, UserShouldExist::class);

        if (!\is_int($userId)) {
            return;
        }

        $user = $this->userRepository->findOneBy(['id' => $userId]);
        if (null === $user) {
            $this->context->buildViolation($constraint->message, ['{{ user_id }}' => $userId])->addViolation();
        }
    }
}
