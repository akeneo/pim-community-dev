<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Validation;

use Akeneo\Category\Application\Query\GetAttribute;
use Akeneo\Category\Domain\Model\Attribute\Attribute;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
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
        Assert::isInstanceOf($constraint, AttributeCodeShouldBeUniqueInTheTemplate::class);
        Assert::stringNotEmpty($value);
        Assert::isInstanceOf($constraint->templateUuid, TemplateUuid::class);

        $attributes = $this->getAttribute->byTemplateUuid($constraint->templateUuid);

        /** @var Attribute $attribute */
        foreach ($attributes as $attribute) {
            if ((string) $attribute->getCode() === $value) {
                $this->context
                    ->buildViolation($constraint->message)
                    ->addViolation();
            }
        }
    }
}
