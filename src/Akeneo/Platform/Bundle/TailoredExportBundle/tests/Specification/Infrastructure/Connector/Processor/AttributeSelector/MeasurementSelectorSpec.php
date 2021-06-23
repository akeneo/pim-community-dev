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

use Akeneo\Pim\Enrichment\Component\Product\Model\MetricInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Tool\Bundle\MeasureBundle\PublicApi\GetUnitTranslations;
use PhpSpec\ObjectBehavior;

class MeasurementSelectorSpec extends ObjectBehavior
{
    public function let(
        GetUnitTranslations $getUnitTranslations
    ) {
        $this->beConstructedWith(['pim_catalog_metric'], $getUnitTranslations);
    }

    public function it_returns_attribute_type_supported()
    {
        $measurementAttribute = $this->createMeasurementAttribute('weight');
        $this->supports(['type' => 'code'], $measurementAttribute)->shouldReturn(true);
        $this->supports(['type' => 'label'], $measurementAttribute)->shouldReturn(true);
        $this->supports(['type' => 'amount'], $measurementAttribute)->shouldReturn(true);
        $this->supports(['type' => 'unknown'], $measurementAttribute)->shouldReturn(false);
    }

    public function it_selects_the_code(
        ValueInterface $value,
        MetricInterface $data,
        ProductInterface $entity
    ) {
        $measurementAttribute = $this->createMeasurementAttribute('weight');
        $value->getData()->willReturn($data);
        $data->getData()->willReturn('42');
        $data->getUnit()->willReturn('GRAM');

        $this->applySelection(['type' => 'code'], $entity, $measurementAttribute, $value)->shouldReturn('GRAM');
    }

    public function it_selects_the_amount(
        ValueInterface $value,
        MetricInterface $data,
        ProductInterface $entity
    ) {
        $measurementAttribute = $this->createMeasurementAttribute('weight');
        $value->getData()->willReturn($data);
        $data->getData()->willReturn('42');
        $data->getUnit()->willReturn('GRAM');

        $this->applySelection(['type' => 'amount'], $entity, $measurementAttribute, $value)->shouldReturn('42');
    }

    public function it_selects_the_label(
        ValueInterface $value,
        GetUnitTranslations $getUnitTranslations,
        MetricInterface $data,
        ProductInterface $entity
    ) {
        $measurementAttribute = $this->createMeasurementAttribute('weight');
        $value->getData()->willReturn($data);
        $data->getData()->willReturn('42');
        $data->getUnit()->willReturn('GRAM');
        $getUnitTranslations->byMeasurementFamilyCodeAndLocale('weight', 'fr_FR')
            ->willReturn([
                'GRAM' => 'Grammes'
            ]);

        $this->applySelection(['type' => 'label', 'locale' => 'fr_FR'], $entity, $measurementAttribute, $value)
            ->shouldReturn('Grammes');
    }

    public function it_selects_the_code_when_label_is_undefined(
        ValueInterface $value,
        GetUnitTranslations $getUnitTranslations,
        MetricInterface $data,
        ProductInterface $entity
    ) {
        $this->beConstructedWith(['pim_catalog_metric'], $getUnitTranslations);
        $measurementAttribute = $this->createMeasurementAttribute('weight');
        $value->getData()->willReturn($data);
        $data->getData()->willReturn('42');
        $data->getUnit()->willReturn('GRAM');
        $getUnitTranslations->byMeasurementFamilyCodeAndLocale('weight', 'fr_FR')
            ->willReturn([
                'GRAM' => null
            ]);

        $this->applySelection(['type' => 'label', 'locale' => 'fr_FR'], $entity, $measurementAttribute, $value)
            ->shouldReturn('[GRAM]');
    }

    private function createMeasurementAttribute(string $measurementFamily): Attribute
    {
        return new Attribute(
            'measurement_attribute',
            'pim_catalog_metric',
            [],
            false,
            false,
            $measurementFamily,
            null,
            null,
            'measurement',
            []
        );
    }
}
