<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation;

use OAuth2\IOAuth2GrantCode;
use OAuth2\Model\IOAuth2AuthCode;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AuthorizationCodeMustBeValidValidator extends ConstraintValidator
{
    public function __construct(private IOAuth2GrantCode $storage)
    {
    }

    public function validate($value, Constraint $constraint)
    {
        if (!\is_string($value)) {
            throw new \InvalidArgumentException('The value to validate must be a string');
        }
        if (!$constraint instanceof AuthorizationCodeMustBeValid) {
            throw new UnexpectedTypeException($constraint, AuthorizationCodeMustBeValid::class);
        }

        /** @var IOAuth2AuthCode|null $authCode */
        $authCode = $this->storage->getAuthCode($value);

        if (null === $authCode) {
            $this->context
                ->buildViolation($constraint->message)
                ->setCause($constraint->cause)
                ->addViolation();
        }
    }
}
