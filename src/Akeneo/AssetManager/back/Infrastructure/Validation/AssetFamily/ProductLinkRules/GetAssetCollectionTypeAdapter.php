<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\ProductLinkRules;

use Akeneo\Pim\Enrichment\AssetManager\Component\AttributeType\AssetCollectionType;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class GetAssetCollectionTypeAdapter implements GetAssetCollectionTypeAdapterInterface
{
    public function __construct(
        private GetAttributes $getAttributes,
    ) {
    }

    public function fetch(string $productAttributeCode): string
    {
        $attribute = $this->getAttributes->forCode($productAttributeCode);

        $this->checkAttributeExists($productAttributeCode, $attribute);
        $this->checkAttributeType($attribute);

        return $attribute->properties()['reference_data_name'];
    }

    private function checkAttributeExists(string $productAttributeCode, ?Attribute $attribute): void
    {
        if (null === $attribute) {
            $message = sprintf('Expected attribute "%s" to exist, none found', $productAttributeCode);
            throw new ProductAttributeDoesNotExistException($message);
        }
    }

    private function checkAttributeType(Attribute $attribute): void
    {
        if ($attribute->type() !== AssetCollectionType::ASSET_COLLECTION) {
            $message = sprintf(
                'Expected attribute "%s" to be of type "%s", "%s" given',
                $attribute->code(),
                AssetCollectionType::ASSET_COLLECTION,
                $attribute->type()
            );
            throw new ProductAttributeCannotContainAssetsException($message);
        }
    }
}
