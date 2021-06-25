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

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\ReferenceEntity\Infrastructure\PublicApi\Enrich\FindRecordsLabelTranslations;
use PhpSpec\ObjectBehavior;

class ReferenceEntitySimpleSelectSelectorSpec extends ObjectBehavior
{
    public function it_returns_attribute_type_supported(
        FindRecordsLabelTranslations $findRecordsLabelTranslations
    ) {
        $this->beConstructedWith(['akeneo_reference_entity'], $findRecordsLabelTranslations);

        $referenceEntitySimpleSelectAttribute = $this->createReferenceEntitySimpleAttribute('marque', 'brand');
        $this->supports(['type' => 'code'], $referenceEntitySimpleSelectAttribute)->shouldReturn(true);
        $this->supports(['type' => 'label'], $referenceEntitySimpleSelectAttribute)->shouldReturn(true);
        $this->supports(['type' => 'unknown'], $referenceEntitySimpleSelectAttribute)->shouldReturn(false);
    }

    public function it_selects_the_code(
        ValueInterface $value,
        FindRecordsLabelTranslations $findRecordsLabelTranslations
    ) {
        $this->beConstructedWith(['akeneo_reference_entity'], $findRecordsLabelTranslations);

        $referenceEntitySimpleSelectAttribute = $this->createReferenceEntitySimpleAttribute('marque', 'brand');
        $value->getData()->willReturn('alessi');

        $this->applySelection(['type' => 'code'], $referenceEntitySimpleSelectAttribute, $value)->shouldReturn('alessi');
    }

    public function it_selects_the_label(
        ValueInterface $value,
        FindRecordsLabelTranslations $findRecordsLabelTranslations
    ) {
        $this->beConstructedWith(['akeneo_reference_entity'], $findRecordsLabelTranslations);

        $referenceEntitySimpleSelectAttribute = $this->createReferenceEntitySimpleAttribute('marque', 'brand');
        $value->getData()->willReturn('alessi');

        $findRecordsLabelTranslations->find('brand', ['alessi'], 'fr_FR')
            ->willReturn(['alessi' => 'Alessi le français']);

        $this->applySelection(['type' => 'label', 'locale' => 'fr_FR'], $referenceEntitySimpleSelectAttribute, $value)
            ->shouldReturn('Alessi le français');
    }

    public function it_selects_the_code_when_label_is_undefined(
        ValueInterface $value,
        FindRecordsLabelTranslations $findRecordsLabelTranslations
    ) {
        $this->beConstructedWith(['akeneo_reference_entity'], $findRecordsLabelTranslations);

        $referenceEntitySimpleSelectAttribute = $this->createReferenceEntitySimpleAttribute('marque', 'brand');
        $value->getData()->willReturn('alessi');

        $findRecordsLabelTranslations->find('brand', ['alessi'], 'fr_FR')
            ->willReturn(['alessi' => null]);

        $this->applySelection(['type' => 'label', 'locale' => 'fr_FR'], $referenceEntitySimpleSelectAttribute, $value)
            ->shouldReturn('[alessi]');
    }

    private function createReferenceEntitySimpleAttribute(string $name, string $referenceEntityIdentifier): Attribute
    {
        return new Attribute(
            $name,
            'akeneo_reference_entity',
            ['reference_data_name' => $referenceEntityIdentifier],
            false,
            false,
            null,
            null,
            null,
            'akeneo_reference_entity',
            []
        );
    }
}
