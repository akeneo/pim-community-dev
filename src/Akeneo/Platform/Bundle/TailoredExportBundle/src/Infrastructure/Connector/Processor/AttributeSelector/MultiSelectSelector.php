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

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\GetExistingAttributeOptionsWithValues;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Platform\TailoredExport\Domain\SelectionTypes;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 */
class MultiSelectSelector implements AttributeSelectorInterface
{
    /** @var string[] */
    private array $supportedAttributeTypes;
    private GetExistingAttributeOptionsWithValues $getExistingAttributeOptionsWithValues;

    public function __construct(
        array $supportedAttributeTypes,
        GetExistingAttributeOptionsWithValues $getExistingAttributeOptionsWithValues
    ) {
        $this->supportedAttributeTypes = $supportedAttributeTypes;
        $this->getExistingAttributeOptionsWithValues = $getExistingAttributeOptionsWithValues;
    }

    public function applySelection(array $selectionConfiguration, Attribute $attribute, ValueInterface $value): string
    {
        $optionsCodes = $value->getData();
        $selectedData = [];

        switch ($selectionConfiguration['type']) {
            case SelectionTypes::CODE:
                $selectedData = $optionsCodes;
                break;
            case SelectionTypes::LABEL:
                $optionsKeys = $this->generateOptionsKeys($optionsCodes, $attribute->code());

                $attributeOptionTranslations = $this->getExistingAttributeOptionsWithValues->fromAttributeCodeAndOptionCodes(
                    $optionsKeys
                );

                $selectedData = array_map(function ($optionCode) use ($attributeOptionTranslations, $attribute, $selectionConfiguration) {
                    $optionKey = $this->generateOptionKey($attribute->code(), $optionCode);
                    return $attributeOptionTranslations[$optionKey][$selectionConfiguration['locale']] ?? sprintf('[%s]', $optionCode);
                }, $value->getData());
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

    private function generateOptionsKeys(array $optionsCodes, string $attributeCode): array
    {
        return array_map(
            function ($optionCode) use ($attributeCode) {
                return $this->generateOptionKey($attributeCode, $optionCode);
            },
            $optionsCodes
        );
    }

    private function generateOptionKey(string $attributeCode, string $optionCode): string
    {
        return sprintf('%s.%s', $attributeCode, $optionCode);
    }
}
