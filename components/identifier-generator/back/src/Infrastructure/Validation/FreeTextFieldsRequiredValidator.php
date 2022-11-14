<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\CommandInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FreeText;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\PropertyInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class FreeTextFieldsRequiredValidator extends ConstraintValidator
{
    public function validate($property, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, FreeTextFieldsRequired::class);
        $command = $this->context->getRoot();
        Assert::isInstanceOf($command, CommandInterface::class);

        if ($property['type'] === FreeText::type() && !isset($target['string'])) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
