<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\AssetManager\Component\Connector\ArrayConverter\FlatToStandard;

use Akeneo\Pim\Enrichment\AssetManager\Component\AttributeType\AssetCollectionType;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Doctrine\Common\Persistence\ObjectRepository;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class EntityWithAssetValues implements ArrayConverterInterface
{
    private ArrayConverterInterface $decoratedConverter;
    private ObjectRepository $attributeRepository;
    private ?array $cachedAssetAttributes = null;

    public function __construct(ArrayConverterInterface $decoratedConverter, ObjectRepository $attributeRepository)
    {
        $this->decoratedConverter = $decoratedConverter;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * Remove the asset "file_path" columns before converting the item.
     */
    public function convert(array $item, array $options = [])
    {
        foreach ($item as $key => $value) {
            $attributeCode = $this->getAttributeCodeFromKey((string) $key);
            $assetAttribute = $this->getAssetAttribute($attributeCode);

            if (null !== $assetAttribute) {
                $pattern = $this->getFilePathPattern($assetAttribute);
                if (1 === preg_match($pattern, trim($key))) {
                    unset($item[$key]);
                }
            }
        }

        return $this->decoratedConverter->convert($item, $options);
    }

    private function getAttributeCodeFromKey(string $key): string
    {
        $split = explode('-', $key);

        return $split[0];
    }

    private function getAssetAttribute(string $attributeCode): ?Attribute
    {
        if (null === $this->cachedAssetAttributes) {
            $this->cachedAssetAttributes = [];
            $assetAttributes = $this->attributeRepository->findBy(['type' => AssetCollectionType::ASSET_COLLECTION]);
            foreach ($assetAttributes as $assetAttribute) {
                $this->cachedAssetAttributes[$assetAttribute->getCode()] = $assetAttribute;
            }
        }

        return $this->cachedAssetAttributes[$attributeCode] ?? null;
    }

    private function getFilePathPattern(AttributeInterface $attribute): string
    {
        if ($attribute->isLocalizable() && $attribute->isScopable()) {
            return '/^[a-zA-Z0-9_]+\-[a-zA-Z0-9_]+\-[a-zA-Z0-9_]+\-file_path$/';
        } elseif (!$attribute->isLocalizable() && !$attribute->isScopable()) {
            return '/^[a-zA-Z0-9_]+\-file_path$/';
        }

        return '/^[a-zA-Z0-9_]+\-[a-zA-Z0-9_]+\-file_path$/';
    }
}
