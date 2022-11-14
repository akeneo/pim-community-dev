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
final class AutoNumberLimitPerStructureValidator extends ConstraintValidator
{
    public function __construct()
    {
    }

    public function validate($structure, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, AutoNumberLimitPerStructure::class);
        $command = $this->context->getRoot();
        Assert::isInstanceOf($command, CommandInterface::class);
        Assert::isArray($structure);

        $countAutonumber = 0;
        foreach ($structure as $property) {
            Assert::isArray($property);
            Assert::keyExists($property, 'type');
            if (AutoNumber::type() === $property['type']) {
                ++$countAutonumber;
            }
        }

        if ($countAutonumber > $constraint->limit) {
            $this->context
                ->buildViolation($constraint->message, ['{{limit}}' => $constraint->limit])
                ->addViolation();
        }
    }
}
