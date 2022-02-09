<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Infrastructure\Validation;

use Akeneo\Pim\Enrichment\Product\Domain\Validator;
use Akeneo\Pim\Enrichment\Product\Domain\Violation;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProxyConstraintValidator extends ConstraintValidator
{
    /** @var array<string, Validator> */
    private array $appValidators;

    public function __construct(iterable $appValidators)
    {
        $this->appValidators = $appValidators instanceof \Traversable
            ? iterator_to_array($appValidators)
            : $appValidators;
        Assert::allImplementsInterface($this->appValidators, Validator::class);
    }


    public function validate($value, Constraint $constraint)
    {
        Assert::isInstanceOf($constraint, ProxyConstraint::class);
        Assert::keyExists($this->appValidators, $constraint->appConstraint());
        $validator = $this->appValidators[$constraint->appConstraint()];

        $violations = $validator->validate($value);
        /** @var Violation $violation */
        foreach ($violations as $violation) {
            $constraintViolationBuilder = $this->context->buildViolation($violation->message(), $violation->messageParameters());
            if (null !== $violation->path()) {
                $constraintViolationBuilder->atPath($violation->path());
            }

            $constraintViolationBuilder->addViolation();
        }
    }
}
