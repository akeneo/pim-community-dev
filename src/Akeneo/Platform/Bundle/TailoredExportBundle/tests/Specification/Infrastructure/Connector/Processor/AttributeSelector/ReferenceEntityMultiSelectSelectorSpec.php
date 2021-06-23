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

namespace Specification\Akeneo\Platform\TailoredExport\Infrastructure\Connector\Processor\AttributeSelector;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\ReferenceEntity\Infrastructure\PublicApi\Enrich\FindRecordsLabelTranslations;
use PhpSpec\ObjectBehavior;

class ReferenceEntityMultiSelectSelectorSpec extends ObjectBehavior
{
    public function let(FindRecordsLabelTranslations $findRecordsLabelTranslations)
    {
        $this->beConstructedWith(['akeneo_reference_entity_collection'], $findRecordsLabelTranslations);
    }

    public function it_returns_attribute_type_supported()
    {
        $referenceEntityMultiSelectAttribute = $this->createReferenceEntityMultiAttribute('marque', 'brand');
        $this->supports(['type' => 'code'], $referenceEntityMultiSelectAttribute)->shouldReturn(true);
        $this->supports(['type' => 'label'], $referenceEntityMultiSelectAttribute)->shouldReturn(true);
        $this->supports(['type' => 'unknown'], $referenceEntityMultiSelectAttribute)->shouldReturn(false);
    }

    public function it_selects_the_code(
        ValueInterface $value,
        FindRecordsLabelTranslations $findRecordsLabelTranslations,
        ProductInterface $entity
    ) {
        $this->beConstructedWith(['akeneo_reference_entity_collection'], $findRecordsLabelTranslations);

        $referenceEntityMultiSelectAttribute = $this->createReferenceEntityMultiAttribute('marque', 'brand');
        $value->getData()->willReturn(['alessi', 'starck', 'jean-paul']);

        $this->applySelection(
            ['type' => 'code', 'separator' => ';'],
            $entity,
            $referenceEntityMultiSelectAttribute,
            $value
        )->shouldReturn('alessi;starck;jean-paul');
    }

    public function it_selects_the_label(
        ValueInterface $value,
        FindRecordsLabelTranslations $findRecordsLabelTranslations,
        ProductInterface $entity
    ) {
        $this->beConstructedWith(['akeneo_reference_entity_collection'], $findRecordsLabelTranslations);

        $referenceEntityMultiSelectAttribute = $this->createReferenceEntityMultiAttribute('marque', 'brand');
        $value->getData()->willReturn(['alessi', 'starck', 'jean-paul']);

        $findRecordsLabelTranslations->find('brand', ['alessi', 'starck', 'jean-paul'], 'fr_FR')
            ->willReturn([
                'alessi' => 'Alessi le français',
                'starck' => 'Philippe Starck',
                'jean-paul' => 'Jean-Paul Deploy',
            ]);

        $this->applySelection(
            ['type' => 'label', 'locale' => 'fr_FR', 'separator' => ','],
            $entity,
            $referenceEntityMultiSelectAttribute,
            $value
        )->shouldReturn('Alessi le français,Philippe Starck,Jean-Paul Deploy');
    }

    public function it_selects_the_code_when_label_is_undefined(
        ValueInterface $value,
        FindRecordsLabelTranslations $findRecordsLabelTranslations,
        ProductInterface $entity
    ) {
        $this->beConstructedWith(['akeneo_reference_entity_collection'], $findRecordsLabelTranslations);

        $referenceEntityMultiSelectAttribute = $this->createReferenceEntityMultiAttribute('marque', 'brand');
        $value->getData()->willReturn(['alessi', 'starck', 'jean-paul']);

        $findRecordsLabelTranslations->find('brand', ['alessi', 'starck', 'jean-paul'], 'fr_FR')
            ->willReturn([
                'alessi' => 'Alessi le français',
                'starck' => null,
                'jean-paul' => 'Jean-Paul Deploy',
            ]);

        $this->applySelection(
            ['type' => 'label', 'locale' => 'fr_FR', 'separator' => '|'],
            $entity,
            $referenceEntityMultiSelectAttribute,
            $value
        )->shouldReturn('Alessi le français|[starck]|Jean-Paul Deploy');
    }

    private function createReferenceEntityMultiAttribute(string $name, string $referenceEntityIdentifier): Attribute
    {
        return new Attribute(
            $name,
            'akeneo_reference_entity_collection',
            ['reference_data_name' => $referenceEntityIdentifier],
            false,
            false,
            null,
            null,
            null,
            'akeneo_reference_entity_collection',
            []
        );
    }
}
