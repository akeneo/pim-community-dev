<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\CommandInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FreeText;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class FreeTextLengthLimitValidator extends ConstraintValidator
{
    public function validate($property, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, FreeTextLengthLimit::class);
        $command = $this->context->getRoot();
        Assert::isInstanceOf($command, CommandInterface::class);
        Assert::isArray($property);
        Assert::keyExists($property, 'type');

        if ('free_text' === $property['type']) {
            Assert::keyExists($property, 'value');
            if (strlen($property['value']) > FreeText::LENGTH_LIMIT) {
                $this->context
                    ->buildViolation($constraint->message, ['{{limit}}' => FreeText::LENGTH_LIMIT])
                    ->addViolation();
            }
        }
    }
}
