<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\CommandInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\AutoNumber;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AutoNumberFieldsRequiredValidator extends ConstraintValidator
{
    public function validate($property, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, AutoNumberFieldsRequired::class);
        $command = $this->context->getRoot();
        Assert::isInstanceOf($command, CommandInterface::class);
        Assert::isArray($property);
        Assert::keyExists($property, 'type');

        if (AutoNumber::type() === $property['type'] && (!isset($property['numberMin']) || !isset($property['digitsMin']))) {
            $this->context
                ->buildViolation($constraint->message, [
                    '{{field}}' => 'numberMin, digitsMin',
                    '{{type}}' => AutoNumber::type(),
                    ])
                ->addViolation();
        }
    }
}
