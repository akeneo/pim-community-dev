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
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Platform\TailoredExport\Domain\SelectionTypes;
use Akeneo\ReferenceEntity\Infrastructure\PublicApi\Enrich\FindRecordsLabelTranslations;

class ReferenceEntityMultiSelectSelector implements AttributeSelectorInterface
{
    /** @var string[] */
    private array $supportedAttributeTypes;
    private FindRecordsLabelTranslations $findRecordsLabelTranslations;

    public function __construct(
        array $supportedAttributeTypes,
        FindRecordsLabelTranslations $findRecordsLabelTranslations
    ) {
        $this->supportedAttributeTypes = $supportedAttributeTypes;
        $this->findRecordsLabelTranslations = $findRecordsLabelTranslations;
    }

    public function applySelection(array $selectionConfiguration, $entity, Attribute $attribute, ValueInterface $value): string
    {
        $recordCodes = array_map('strval', $value->getData());
        $referenceEntityIdentifier = $attribute->properties()['reference_data_name'] ?? null;

        if (null === $referenceEntityIdentifier) {
            throw new \LogicException('Reference entity identifier not present in the attribute properties ("reference_data_name")');
        }

        switch ($selectionConfiguration['type']) {
            case SelectionTypes::CODE:
                $selectedData = $recordCodes;
                break;
            case SelectionTypes::LABEL:
                $recordTranslations = $this->findRecordsLabelTranslations->find(
                    $referenceEntityIdentifier,
                    $recordCodes,
                    $selectionConfiguration['locale']
                );

                $selectedData = array_map(fn ($recordCode) => $recordTranslations[$recordCode] ??
                    sprintf('[%s]', $recordCode), $recordCodes);

                break;
            default:
                throw new \LogicException(sprintf('Selection type "%s" is not supported', $selectionConfiguration['type']));
        }

        return implode($selectionConfiguration['separator'], $selectedData);
    }

    public function supports(array $selectionConfiguration, Attribute $attribute): bool
    {
        return in_array($selectionConfiguration['type'], [SelectionTypes::LABEL, SelectionTypes::CODE])
            && in_array($attribute->type(), $this->supportedAttributeTypes);
    }
}
