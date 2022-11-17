<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Update\UpdateGeneratorCommand;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\IdentifierGeneratorRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CodeAttributeShouldExistValidator extends ConstraintValidator
{
    public function __construct(private IdentifierGeneratorRepository $identifierGeneratorRepository)
    {
    }

    public function validate($updateGeneratorCommand, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, CodeAttributeShouldExist::class);
        if (!$updateGeneratorCommand instanceof UpdateGeneratorCommand) {
            return;
        }

        $identifierGenerator = $this->identifierGeneratorRepository->get($updateGeneratorCommand->code);
        if (null === $identifierGenerator) {
            $this->context
                ->buildViolation($constraint->message, ['{{code}}' => $updateGeneratorCommand->code])
                ->addViolation();
        }
    }
}
