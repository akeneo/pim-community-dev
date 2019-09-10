<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\ProductLinkRules;

use Akeneo\Pim\Enrichment\AssetManager\Component\AttributeType\AssetMultipleLinkType;
use Akeneo\Pim\Structure\Component\Model\AbstractAttribute;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class FindAssetCollectionTypeACL implements FindAssetCollectionTypeACLInterface
{
    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    public function __construct(AttributeRepositoryInterface $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    public function fetch(string $productAttributeCode): string
    {
        /** @var AbstractAttribute $attribute */
        $attribute = $this->attributeRepository->findOneByIdentifier($productAttributeCode);
        $this->checkAttributeType($attribute);

        return $attribute->getReferenceDataName();
    }

    private function checkAttributeType(AbstractAttribute $attribute): void
    {
        if ($attribute->getType() !== AssetMultipleLinkType::ASSET_MULTIPLE_LINK) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected attribute "%s" to be of type "%s", "%s" given',
                    $attribute->getCode(),
                    AssetMultipleLinkType::ASSET_MULTIPLE_LINK,
                    $attribute->getType()
                )
            );
        }
    }
}
