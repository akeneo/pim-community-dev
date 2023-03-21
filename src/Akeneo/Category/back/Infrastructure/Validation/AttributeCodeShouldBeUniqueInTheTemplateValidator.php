<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Validation;

use Akeneo\Category\Application\Command\AddAttributeCommand;
use Akeneo\Category\Application\Query\GetAttribute;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class AttributeCodeShouldBeUniqueInTheTemplateValidator extends ConstraintValidator
{
    public function __construct(
        private readonly GetAttribute $getAttribute,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (empty($value)) {
            return;
        }

        Assert::isInstanceOf($constraint, AttributeCodeShouldBeUniqueInTheTemplate::class);

        /** @var AddAttributeCommand $command */
        $command = $this->context->getObject();
        Assert::isInstanceOf($command, AddAttributeCommand::class);

        $templateUuid = $command->templateUuid;
        Assert::uuid($templateUuid);

        $attributeCollection = $this->getAttribute->byTemplateUuid(TemplateUuid::fromString($templateUuid));

        $attributes = $attributeCollection->getAttributes();

        if (empty($attributes)) {
            return;
        }

        foreach ($attributes as $attribute) {
            if ((string) $attribute->getCode() === $value) {
                $this->context
                    ->buildViolation($constraint->message, ['{{ attributeCode }}' => $value])
                    ->addViolation();
            }
        }
    }
}
