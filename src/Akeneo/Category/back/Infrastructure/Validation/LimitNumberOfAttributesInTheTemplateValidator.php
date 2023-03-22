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
final class LimitNumberOfAttributesInTheTemplateValidator extends ConstraintValidator
{
    private const ERROR_CODE = 'attributes_limit_reached';

    public function __construct(
        private readonly GetAttribute $getAttribute,
    ) {
    }

    /**
     * @param AddAttributeCommand $value
     */
    public function validate($value, Constraint $constraint): void
    {
        Assert::isInstanceOf($value, AddAttributeCommand::class);
        Assert::isInstanceOf($constraint, LimitNumberOfAttributesInTheTemplate::class);

        $templateUuid = $value->templateUuid;
        Assert::uuid($templateUuid);

        $attributeCollection = $this->getAttribute->byTemplateUuid(TemplateUuid::fromString($templateUuid));

        if ($attributeCollection->count() >= 50) {
            $this->context
                ->buildViolation($constraint->message)
                ->setCode(self::ERROR_CODE)
                ->addViolation();
        }
    }
}
