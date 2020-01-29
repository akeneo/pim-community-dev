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

namespace Akeneo\Pim\Enrichment\AssetManager\Component\Connector\ArrayConverter\StandardToFlat;

use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLinkAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\NumberAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\StandardToFlat\AbstractSimpleArrayConverter;

class Asset extends AbstractSimpleArrayConverter
{
    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var AbstractAttribute[] */
    private $cachedAttributes = [];

    public function __construct(AttributeRepositoryInterface $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    protected function convertProperty($property, $data, array $convertedItem, array $options)
    {
        switch ($property) {
            case 'code':
            case 'assetFamilyIdentifier':
                $convertedItem[$property] = $data;
                break;
            case 'values':
                $convertedItem = $this->convertValues($convertedItem, $data);
                break;
            case 'identifier': // we don't expose the identifier
            default:
                break;
        }

        return $convertedItem;
    }

    private function convertValues(array $convertedItem, array $values): array
    {
        foreach ($values as $value) {
            $attribute = $this->getAttribute($value['attribute']);
            $key = $this->generateKey($attribute, $value);
            $data = null;

            switch ($attribute->getType()) {
                case NumberAttribute::ATTRIBUTE_TYPE:
                case TextAttribute::ATTRIBUTE_TYPE:
                case OptionAttribute::ATTRIBUTE_TYPE:
                    $data = $value['data'];
                    break;
                case OptionCollectionAttribute::ATTRIBUTE_TYPE:
                    $data = implode(',', $value['data']);
                    break;
                case MediaLinkAttribute::ATTRIBUTE_TYPE:
                    $data = sprintf(
                        '%s%s%s',
                        $attribute->getPrefix()->stringValue(),
                        $value['data'],
                        $attribute->getSuffix()->stringValue()
                    );
                    break;
                case MediaFileAttribute::ATTRIBUTE_TYPE:
                    $data = $value['data']['filePath'];
                    break;
                default:
                    break;
            }
            $convertedItem[$key] = $data;
        }

        return $convertedItem;
    }

    private function getAttribute(string $attributeIdentifier): AbstractAttribute
    {
        if (!isset($this->cachedAttributes[$attributeIdentifier])) {
            $this->cachedAttributes[$attributeIdentifier] = $this->attributeRepository->getByIdentifier(
                AttributeIdentifier::fromString($attributeIdentifier)
            );
        }

        return $this->cachedAttributes[$attributeIdentifier];
    }

    private function generateKey(AbstractAttribute $attribute, array $value): string
    {
        $key = $attribute->getCode()->__toString();
        if ($attribute->hasValuePerChannel()) {
            $key = sprintf('%s-%s', $key, $value['channel']);
        }
        if ($attribute->hasValuePerLocale()) {
            $key = sprintf('%s-%s', $key, $value['locale']);
        }

        return $key;
    }
}
