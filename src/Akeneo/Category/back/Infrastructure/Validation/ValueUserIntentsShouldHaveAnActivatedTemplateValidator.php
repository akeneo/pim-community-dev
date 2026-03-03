<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Validation;

use Akeneo\Category\Api\Command\UserIntents\UserIntent;
use Akeneo\Category\Api\Command\UserIntents\ValueUserIntent;
use Akeneo\Category\Application\Query\GetAttribute;
use Akeneo\Category\Application\Query\IsTemplateDeactivated;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\AbstractValue;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class ValueUserIntentsShouldHaveAnActivatedTemplateValidator extends ConstraintValidator
{
    private const ERROR_CODE = 'deactivated_template';

    public function __construct(
        private readonly GetAttribute $getAttribute,
        private readonly IsTemplateDeactivated $isTemplateDeactivated,
    ) {
    }

    /**
     * @param array<UserIntent> $value
     */
    public function validate($value, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, ValueUserIntentsShouldHaveAnActivatedTemplate::class);
        Assert::isArray($value);
        Assert::allImplementsInterface($value, UserIntent::class);

        $this->validateAttributesLinkedToActivatedTemplate($value, $constraint);
    }

    /**
     * @param UserIntent[] $valueUserIntents
     */
    private function validateAttributesLinkedToActivatedTemplate(array $valueUserIntents, Constraint $constraint): void
    {
        /** @var ValueUserIntent[] $valueUserIntents */
        $valueUserIntents = array_values(array_filter($valueUserIntents, function ($userIntent) {
            return is_subclass_of($userIntent, ValueUserIntent::class);
        }));

        if (empty($valueUserIntents)) {
            return;
        }

        $firstValue = $valueUserIntents[0];
        $attributeCollection = $this->getAttribute->byUuids([AttributeUuid::fromString($firstValue->attributeUuid())]);
        $attribute = $attributeCollection->getAttributeByIdentifier(
            sprintf('%s%s%s', $firstValue->attributeCode(), AbstractValue::SEPARATOR, $firstValue->attributeUuid()),
        );
        $isTemplateDeactivated = ($this->isTemplateDeactivated)($attribute->getTemplateUuid());

        if (!$isTemplateDeactivated) {
            return;
        }

        $this->context
            ->buildViolation($constraint->message)
            ->setCode(self::ERROR_CODE)
            ->addViolation();
    }
}
