<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedEntity;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\ReplaceAssociatedQuantifiedProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\ReplaceAssociatedQuantifiedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\ReplaceAssociatedQuantifiedProductUuids;
use Akeneo\Pim\Enrichment\Product\Domain\StandardFormat\Validator\QuantifiedAssociationsStructureValidator;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class QuantifiedAssociationUserIntentFactory implements UserIntentFactory
{
    use ValidateDataTrait;

    public function __construct(
        private QuantifiedAssociationsStructureValidator $quantifiedAssociationsStructureValidator
    ) {
    }

    public function getSupportedFieldNames(): array
    {
        return ['quantified_associations'];
    }

    /**
     * @inheritDoc
     */
    public function create(string $fieldName, mixed $data): array
    {
        $this->quantifiedAssociationsStructureValidator->validate($fieldName, $data);
        $userIntents = [];
        foreach ($data as $associationType => $associations) {
            if (\array_key_exists('product_uuids', $associations)) {
                $productQuantityValues = \array_map(
                    fn ($association) => new QuantifiedEntity($association['uuid'], $association['quantity']),
                    $associations['product_uuids']
                );
                $userIntents[] = new ReplaceAssociatedQuantifiedProductUuids((string)$associationType, $productQuantityValues);
            }
            if (\array_key_exists('products', $associations)) {
                $productQuantityValues = \array_map(
                    fn ($association) => new QuantifiedEntity($association['identifier'], $association['quantity']),
                    $associations['products']
                );
                $userIntents[] = new ReplaceAssociatedQuantifiedProducts((string)$associationType, $productQuantityValues);
            }
            if (\array_key_exists('product_models', $associations)) {
                $productModelQuantityValues = \array_map(
                    fn ($association) => new QuantifiedEntity($association['identifier'], $association['quantity']),
                    $associations['product_models']
                );
                $userIntents[] = new ReplaceAssociatedQuantifiedProductModels((string)$associationType, $productModelQuantityValues);
            }
        }

        return $userIntents;
    }
}
