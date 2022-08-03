<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Infrastructure\Validation;

use Akeneo\Pim\Enrichment\Category\API\Query\GetOwnedCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier as ProductIdentifierValueObject;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid;
use Akeneo\Pim\Enrichment\Product\Domain\Model\ProductIdentifier;
use Akeneo\Pim\Enrichment\Product\Domain\Model\ViolationCode;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetCategoryCodes;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * Check the user has own permission on the product
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class IsUserOwnerOfTheProductValidator extends ConstraintValidator
{
    public function __construct(
        private GetCategoryCodes $getCategoryCodes,
        private GetOwnedCategories $getOwnedCategories,
    ) {
    }

    public function validate($command, Constraint $constraint): void
    {
        Assert::isInstanceOf($command, UpsertProductCommand::class);
        Assert::isInstanceOf($constraint, IsUserOwnerOfTheProduct::class);

        $productCategoryCodes = null;
        if ($command->identifierOrUuid() instanceof ProductIdentifierValueObject) {
            $productCategoryCodes = $this->getProductCategoryCodesFromIdentifier($command->identifierOrUuid()->identifier());
        } elseif ($command->identifierOrUuid() instanceof ProductUuid) {
            $productCategoryCodes = $this->getCategoryCodes->fromProductUuids([
                    $command->identifierOrUuid()->uuid()
                ])[$command->identifierOrUuid()->uuid()->toString()] ?? null;
        } elseif (\is_string($command->identifierOrUuid())) {
            $productCategoryCodes = $this->getProductCategoryCodesFromIdentifier($command->identifierOrUuid());
        }

        if (null === $productCategoryCodes || [] === $productCategoryCodes) {
            return;
        }

        if ([] === $this->getOwnedCategories->forUserId($productCategoryCodes, $command->userId())) {
            $this->context->buildViolation($constraint->message)
                ->setCode((string) (ViolationCode::buildGlobalViolationCode(ViolationCode::USER_IS_NOT_OWNER, ViolationCode::PERMISSION)))
                ->atPath('userId')
                ->addViolation();
        }
    }

    /**
     * @return mixed|string[]|void|null
     */
    private function getProductCategoryCodesFromIdentifier(string $identifier)
    {
        try {
            $productIdentifier = ProductIdentifier::fromString($identifier);
        } catch (\InvalidArgumentException) {
            return;
        }
        return $this->getCategoryCodes->fromProductIdentifiers([$productIdentifier])[$productIdentifier->asString()] ?? null;
    }
}
