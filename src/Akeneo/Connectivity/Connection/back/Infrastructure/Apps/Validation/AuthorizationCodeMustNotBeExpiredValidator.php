<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation;

use OAuth2\IOAuth2GrantCode;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AuthorizationCodeMustNotBeExpiredValidator extends ConstraintValidator
{
    public function __construct(private IOAuth2GrantCode $storage)
    {
    }

    public function validate($value, Constraint $constraint)
    {
        if (!\is_string($value)) {
            throw new \InvalidArgumentException('The value to validate must be a string');
        }
        if (!$constraint instanceof AuthorizationCodeMustNotBeExpired) {
            throw new UnexpectedTypeException($constraint, AuthorizationCodeMustNotBeExpired::class);
        }

        $authCode = $this->storage->getAuthCode($value);

        if ($authCode !== null && $authCode->hasExpired()) {
            $this->context
                ->buildViolation($constraint->message)
                ->setCause($constraint->cause)
                ->addViolation();
        }
    }
}
