<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Infrastructure\Validation;

use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent;
use Akeneo\Pim\Enrichment\Product\Domain\Model\ViolationCode;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Permission\IsAttributeEditable;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AttributeGroupShouldBeEditableValidator extends ConstraintValidator
{
    public function __construct(private IsAttributeEditable $isAttributeEditable)
    {
    }

    public function validate($valueUserIntent, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, AttributeGroupShouldBeEditable::class);
        Assert::isInstanceOf($valueUserIntent, ValueUserIntent::class);

        $command = $this->context->getRoot();
        Assert::isInstanceOf($command, UpsertProductCommand::class);

        if (-1 === (int) $command->userId()) {
            return;
        }

        $isEditable = $this->isAttributeEditable->forCode($valueUserIntent->attributeCode(), $command->userId());

        if (!$isEditable) {
            $this->context->buildViolation(
                $constraint->message,
                [ '{{ attributeCode }}' => $valueUserIntent->attributeCode()]
            )
                ->setCode((string) (ViolationCode::buildGlobalViolationCode(ViolationCode::USER_CANNOT_EDIT_ATTRIBUTE, ViolationCode::PERMISSION)))
                ->addViolation();
        }
    }
}
