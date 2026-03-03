<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Infrastructure\Validation;

use Akeneo\Pim\Enrichment\Product\Domain\Model\ViolationCode;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UserShouldExistValidator extends ConstraintValidator
{
    public function __construct(private UserRepositoryInterface $userRepository)
    {
    }

    public function validate($userId, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, UserShouldExist::class);

        if (!\is_int($userId) || -1 === (int) $userId) {
            return;
        }

        $user = $this->userRepository->findOneBy(['id' => $userId]);
        if (null === $user) {
            $this->context->buildViolation($constraint->message, ['{{ user_id }}' => $userId])
                ->setCode((string) ViolationCode::PERMISSION)
                ->addViolation();
        }
    }
}
