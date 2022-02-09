<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Api\Validation;

use Akeneo\Pim\Enrichment\Category\API\Query\GetOwnedCategories;
use Akeneo\Pim\Enrichment\Product\Api\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\Domain\Model\ProductIdentifier;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetCategoryCodes;
use Akeneo\Pim\Enrichment\Product\Domain\Validator;
use Akeneo\Pim\Enrichment\Product\Domain\Violation;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class IsUserOwnerOfTheProduct implements Validator
{
    private string $message = 'pim_enrich.product.validation.upsert.category_no_access_to_products';

    public function __construct(
        private GetCategoryCodes $getCategoryCodes,
        private GetOwnedCategories $getOwnedCategories
    ) {
    }

    public function validate($command): array
    {
        Assert::isInstanceOf($command, UpsertProductCommand::class);

        try {
            $productIdentifier = ProductIdentifier::fromString($command->productIdentifier());
        } catch (\InvalidArgumentException) {
            return [];
        }

        $productCategoryCodes = $this->getCategoryCodes->fromProductIdentifiers([$productIdentifier])[$productIdentifier->asString()] ?? null;
        if (null === $productCategoryCodes || [] === $productCategoryCodes) {
            // null => product does not exist
            // [] => product exists and has no category
            // A new product without category is always granted (from a category permission point of view).
            // TODO later: if we create/add with a category, we have to check the category is granted
            return [];
        }

        if ([] === $this->getOwnedCategories->forUserId($productCategoryCodes, $command->userId())) {
            return [Violation::fromMessageAndPath($this->message)];
        }

        return [];
    }
}
