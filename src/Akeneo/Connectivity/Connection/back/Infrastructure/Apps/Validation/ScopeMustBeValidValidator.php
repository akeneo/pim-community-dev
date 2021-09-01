<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation;

use Akeneo\Tool\Bundle\ApiBundle\Security\ScopeMapper;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ScopeMustBeValidValidator extends ConstraintValidator
{
    private ScopeMapper $scopeMapper;

    public function __construct(ScopeMapper $scopeMapper)
    {
        $this->scopeMapper = $scopeMapper;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof ScopeMustBeValid) {
            throw new UnexpectedTypeException($constraint, ScopeMustBeValid::class);
        }

        if (empty($value)) {
            return;
        }

        $scopes = explode(' ', (string) $value);
        $validScopes = $this->scopeMapper->getAllScopes();

        foreach ($scopes as $scope) {
            if (!in_array($scope, $validScopes)) {
                $this->context->buildViolation($constraint->message)->addViolation();

                return;
            }
        }
    }
}
