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
class SimpleSelectSelector implements AttributeSelectorInterface
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

    public function applySelection(array $selectionConfiguration, $entity, Attribute $attribute, ValueInterface $value): string
    {
        $optionCode = $value->getData();

        switch ($selectionConfiguration['type']) {
            case SelectionTypes::CODE:
                return $optionCode;
            case SelectionTypes::LABEL:
                $optionKey = sprintf('%s.%s', $attribute->code(), $optionCode);
                $attributeOptionTranslations = $this->getExistingAttributeOptionsWithValues->fromAttributeCodeAndOptionCodes(
                    [$optionKey]
                );

                return $attributeOptionTranslations[$optionKey][$selectionConfiguration['locale']] ?? sprintf('[%s]', $optionCode);
            default:
                throw new \LogicException(sprintf('Selection type "%s" is not supported', $selectionConfiguration['type']));
        }
    }

    public function supports(array $selectionConfiguration, Attribute $attribute): bool
    {
        return in_array($selectionConfiguration['type'], [SelectionTypes::LABEL, SelectionTypes::CODE])
            && in_array($attribute->type(), $this->supportedAttributeTypes);
    }
}
