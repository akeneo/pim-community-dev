<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Infrastructure\Connector\Processor\AttributeSelector;

use Akeneo\AssetManager\Infrastructure\PublicApi\Enrich\FindAssetLabelTranslation;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Platform\TailoredExport\Domain\SelectionTypes;

class AssetCollectionSelector implements AttributeSelectorInterface
{
    /** @var string[] */
    private array $supportedAttributeTypes;
    private FindAssetLabelTranslation $findAssetLabelTranslations;

    public function __construct(
        array $supportedAttributeTypes,
        FindAssetLabelTranslation $findAssetLabelTranslations
    ) {
        $this->supportedAttributeTypes = $supportedAttributeTypes;
        $this->findAssetLabelTranslations = $findAssetLabelTranslations;
    }

    public function applySelection(array $selectionConfiguration, Attribute $attribute, ValueInterface $value): string
    {
        $assetCodes = array_map('strval', $value->getData());
        $assetFamilyIdentifier = $attribute->properties()['reference_data_name'] ?? null;

        if (null === $assetFamilyIdentifier) {
            throw new \LogicException('Asset family identifier not present in the attribute properties ("reference_data_name")');
        }

        $selectedData = [];

        switch ($selectionConfiguration['type']) {
            case SelectionTypes::CODE:
                $selectedData = $assetCodes;
                break;
            case SelectionTypes::LABEL:
                $assetTranslations = $this->findAssetLabelTranslations->byFamilyCodeAndAssetCodes(
                    $assetFamilyIdentifier,
                    $assetCodes,
                    $selectionConfiguration['locale']
                );

                $selectedData = array_map(fn ($assetCode) => $assetTranslations[$assetCode] ??
                    sprintf('[%s]', $assetCode), $assetCodes);

                break;
            default:
                throw new \LogicException(sprintf('Selection type "%s" is not supported', $selectionConfiguration['type']));
        }

        return implode(', ', $selectedData);
    }

    public function supports(array $selectionConfiguration, Attribute $attribute): bool
    {
        return in_array($selectionConfiguration['type'], [SelectionTypes::LABEL, SelectionTypes::CODE])
            && in_array($attribute->type(), $this->supportedAttributeTypes);
    }
}
