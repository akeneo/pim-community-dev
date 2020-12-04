<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Component\Query\InternalApi\IsAttributeCodeBlacklistedInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class BlacklistedAttributeCodeValidator extends ConstraintValidator
{
    protected IsAttributeCodeBlacklistedInterface $isAttributeCodeBlacklisted;

    public function __construct(IsAttributeCodeBlacklistedInterface $isAttributeCodeBlacklisted)
    {
        $this->isAttributeCodeBlacklisted = $isAttributeCodeBlacklisted;
    }

    /**
     * Don't allow creating an attribute if it's code is blacklisted
     *
     * @param string $attributeCode
     * @throws \Exception
     */
    public function validate($attributeCode, Constraint $constraint): void
    {
        if (!$constraint instanceof BlacklistedAttributeCode) {
            throw new UnexpectedTypeException($constraint, BlacklistedAttributeCode::class);
        }

        if (is_string($attributeCode) && $this->isAttributeCodeBlacklisted->execute($attributeCode)) {
            $this->context->buildViolation($constraint->message, ['%attribute_code%' => $attributeCode])
                ->addViolation();
        }
    }
}
