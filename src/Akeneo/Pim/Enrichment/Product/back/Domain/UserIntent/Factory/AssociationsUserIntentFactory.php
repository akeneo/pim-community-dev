<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\ReplaceAssociatedGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\ReplaceAssociatedProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\ReplaceAssociatedProducts;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationsUserIntentFactory implements UserIntentFactory
{
    use ValidateDataTrait;

    public function getSupportedFieldNames(): array
    {
        return ['associations'];
    }

    /**
     * @param string $fieldName
     * @param mixed $data
     *
     * example of $data
     * ['PACK' => [
    'groups' => [],
    'product_models' => [],
    'products' => [],
    ],]
     */
    public function create(string $fieldName, mixed $data): array
    {
        if (!\is_array($data)) {
            throw InvalidPropertyTypeException::arrayExpected($fieldName, static::class, $data);
        }
        $userIntents = [];
        foreach($data as $associationType => $associations) {
            if (!is_array($associations)) {
                throw InvalidPropertyTypeException::arrayExpected(
                    'associations',
                    static::class,
                    $associations
                );
            }
            foreach ($associations as $associationsByEntity) {
                $this->validateScalarArray('association', $associationsByEntity);
            }
            if (\array_key_exists('products', $associations)) {
                $userIntents[] = new ReplaceAssociatedProducts($associationType, $associations['products']);
            }
            if (\array_key_exists('product_models', $associations)) {
                $userIntents[] = new ReplaceAssociatedProductModels($associationType, $associations['product_models']);
            }
            if (\array_key_exists('groups', $associations)) {
                $userIntents[] = new ReplaceAssociatedGroups($associationType, $associations['groups']);
            }
        }

        return $userIntents;
    }
}
