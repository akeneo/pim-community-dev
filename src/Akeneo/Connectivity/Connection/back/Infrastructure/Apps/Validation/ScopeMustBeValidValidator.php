<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation;

use Akeneo\Tool\Component\Api\Security\ScopeMapperRegistry;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ScopeMustBeValidValidator extends ConstraintValidator
{
    private ScopeMapperRegistry $scopeMapperRegistry;

    public function __construct(ScopeMapperRegistry $scopeMapperRegistry)
    {
        $this->scopeMapperRegistry = $scopeMapperRegistry;
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
        $validScopes = $this->scopeMapperRegistry->getAllScopes();

        foreach ($scopes as $scope) {
            if (!in_array($scope, $validScopes)) {
                $this->context->buildViolation($constraint->message)->addViolation();

                return;
            }
        }
    }
}
