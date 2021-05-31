<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\ProductLinkRules;

use Akeneo\Pim\Enrichment\AssetManager\Component\AttributeType\AssetCollectionType;
use Akeneo\Pim\Structure\Component\Model\AbstractAttribute;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class GetAssetCollectionTypeAdapter implements GetAssetCollectionTypeAdapterInterface
{
    private AttributeRepositoryInterface $attributeRepository;

    public function __construct(AttributeRepositoryInterface $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    public function fetch(string $productAttributeCode): string
    {
        /** @var AbstractAttribute $attribute */
        $attribute = $this->attributeRepository->findOneByIdentifier($productAttributeCode);
        $this->checkAttributeExists($productAttributeCode, $attribute);
        $this->checkAttributeType($attribute);

        return $attribute->getReferenceDataName();
    }

    private function checkAttributeExists(string $productAttributeCode, ?AbstractAttribute $attribute): void
    {
        if (null === $attribute) {
            $message = sprintf('Expected attribute "%s" to exist, none found', $productAttributeCode);
            throw new ProductAttributeDoesNotExistException($message);
        }
    }

    private function checkAttributeType(AbstractAttribute $attribute): void
    {
        if ($attribute->getType() !== AssetCollectionType::ASSET_COLLECTION) {
            $message = sprintf(
                'Expected attribute "%s" to be of type "%s", "%s" given',
                $attribute->getCode(),
                AssetCollectionType::ASSET_COLLECTION,
                $attribute->getType()
            );
            throw new ProductAttributeCannotContainAssetsException($message);
        }
    }
}
