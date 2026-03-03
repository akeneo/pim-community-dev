<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Exception\CouldNotFindIdentifierGeneratorException;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\IdentifierGeneratorRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UniqueIdentifierGeneratorCodeValidator extends ConstraintValidator
{
    public function __construct(
        private readonly IdentifierGeneratorRepository $repository
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, UniqueIdentifierGeneratorCode::class);
        if (!\is_string($value)) {
            return;
        }

        try {
            $this->repository->get($value);
        } catch (CouldNotFindIdentifierGeneratorException) {
            return;
        }

        $this->context->buildViolation($constraint->message)->addViolation();
    }
}
