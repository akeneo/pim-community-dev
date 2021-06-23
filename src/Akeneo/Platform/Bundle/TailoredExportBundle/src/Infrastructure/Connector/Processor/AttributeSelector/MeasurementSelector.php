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
use Akeneo\Tool\Bundle\MeasureBundle\PublicApi\GetUnitTranslations;

class MeasurementSelector implements AttributeSelectorInterface
{
    /** @var string[] */
    private array $supportedAttributeTypes;
    private GetUnitTranslations $getUnitTranslations;

    public function __construct(
        array $supportedAttributeTypes,
        GetUnitTranslations $getUnitTranslations
    ) {
        $this->supportedAttributeTypes = $supportedAttributeTypes;
        $this->getUnitTranslations = $getUnitTranslations;
    }

    public function applySelection(array $selectionConfiguration, $entity, Attribute $attribute, ValueInterface $value): string
    {
        $data = $value->getData();

        switch ($selectionConfiguration['type']) {
            case SelectionTypes::CODE:
                return $data->getUnit() ?? '';
            case SelectionTypes::AMOUNT:
                return (string) ($data->getData() ?? '');
            case SelectionTypes::LABEL:
                $unit = $data->getUnit();

                if (null === $unit) {
                    return '';
                }

                $unitTranslations = $this->getUnitTranslations->byMeasurementFamilyCodeAndLocale(
                    $attribute->metricFamily(),
                    $selectionConfiguration['locale']
                );

                return $unitTranslations[$unit] ?? sprintf('[%s]', $unit);
            default:
                throw new \LogicException(sprintf('Selection type "%s" is not supported', $selectionConfiguration['type']));
        }
    }

    public function supports(array $selectionConfiguration, Attribute $attribute): bool
    {
        return in_array($selectionConfiguration['type'], [SelectionTypes::LABEL, SelectionTypes::CODE, SelectionTypes::AMOUNT])
            && in_array($attribute->type(), $this->supportedAttributeTypes);
    }
}
