<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Infrastructure\Validation;

use Akeneo\Pim\Enrichment\Category\API\Query\GetOwnedCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Query\GetProductUuids;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid;
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
        private GetProductUuids $getProductUuids,
    ) {
    }

    public function validate($command, Constraint $constraint): void
    {
        Assert::isInstanceOf($command, UpsertProductCommand::class);
        Assert::isInstanceOf($constraint, IsUserOwnerOfTheProduct::class);

        $uuid = null;
        if ($command->productIdentifierOrUuid() instanceof ProductIdentifier) {
            $uuid = $this->getProductUuids->fromIdentifier($command->productIdentifierOrUuid()->identifier());
        } elseif ($command->productIdentifierOrUuid() instanceof ProductUuid) {
            $uuid = $command->productIdentifierOrUuid()->uuid();
        }

        if (null === $uuid) {
            return;
        }

        $productCategoryCodes = $this->getCategoryCodes->fromProductUuids([$uuid])[$uuid->toString()] ?? null;
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
}
