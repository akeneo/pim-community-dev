<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation;

use Akeneo\Connectivity\Connection\Application\Apps\AppAuthorizationSessionInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ClientIdMustHaveOngoingAuthorizationValidator extends ConstraintValidator
{
    public function __construct(private AppAuthorizationSessionInterface $session)
    {
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof ClientIdMustHaveOngoingAuthorization) {
            throw new UnexpectedTypeException($constraint, ClientIdMustHaveOngoingAuthorization::class);
        }

        $appAuthorization = $this->session->getAppAuthorization((string) $value);

        if (null === $appAuthorization) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
