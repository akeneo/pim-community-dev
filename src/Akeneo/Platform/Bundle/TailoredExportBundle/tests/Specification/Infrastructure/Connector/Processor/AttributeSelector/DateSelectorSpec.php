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
use PhpSpec\ObjectBehavior;

class DateSelectorSpec extends ObjectBehavior
{
    public function it_returns_attribute_type_supported()
    {
        $this->beConstructedWith(['pim_catalog_date']);

        $dateAttribute = $this->createAttribute('pim_catalog_date');
        $this->supports(['format' => 'yyyy-mm-dd'], $dateAttribute)->shouldReturn(true);

        $booleanAttribute = $this->createAttribute('pim_catalog_boolean');
        $this->supports(['format' => 'yyyy-mm-dd'], $booleanAttribute)->shouldReturn(false);
    }

    public function it_selects_the_date_properly_formatted(ValueInterface $value)
    {
        $this->beConstructedWith(['pim_catalog_date']);

        $dateAttribute = $this->createAttribute('pim_catalog_date');
        $value->getData()->willReturn(\DateTime::createFromFormat('Y/m/d', '1989/05/08'));

        $this->applySelection(['format' => 'yyyy-mm-dd'], $dateAttribute, $value)->shouldReturn('1989-05-08');
        $this->applySelection(['format' => 'yyyy/mm/dd'], $dateAttribute, $value)->shouldReturn('1989/05/08');
        $this->applySelection(['format' => 'yyyy.mm.dd'], $dateAttribute, $value)->shouldReturn('1989.05.08');
        $this->applySelection(['format' => 'yy.m.dd'], $dateAttribute, $value)->shouldReturn('89.5.08');
        $this->applySelection(['format' => 'mm-dd-yyyy'], $dateAttribute, $value)->shouldReturn('05-08-1989');
        $this->applySelection(['format' => 'mm/dd/yyyy'], $dateAttribute, $value)->shouldReturn('05/08/1989');
        $this->applySelection(['format' => 'dd-mm-yyyy'], $dateAttribute, $value)->shouldReturn('08-05-1989');
        $this->applySelection(['format' => 'dd/mm/yyyy'], $dateAttribute, $value)->shouldReturn('08/05/1989');
        $this->applySelection(['format' => 'dd.mm.yyyy'], $dateAttribute, $value)->shouldReturn('08.05.1989');
        $this->applySelection(['format' => 'mm.dd.yyyy'], $dateAttribute, $value)->shouldReturn('05.08.1989');
        $this->applySelection(['format' => 'dd-mm-yy'], $dateAttribute, $value)->shouldReturn('08-05-89');
        $this->applySelection(['format' => 'dd/mm/yy'], $dateAttribute, $value)->shouldReturn('08/05/89');
        $this->applySelection(['format' => 'dd.mm.yy'], $dateAttribute, $value)->shouldReturn('08.05.89');
        $this->applySelection(['format' => 'dd-m-yy'], $dateAttribute, $value)->shouldReturn('08-5-89');
        $this->applySelection(['format' => 'dd/m/yy'], $dateAttribute, $value)->shouldReturn('08/5/89');
        $this->applySelection(['format' => 'dd.m.yy'], $dateAttribute, $value)->shouldReturn('08.5.89');
    }

    private function createAttribute(string $attributeType): Attribute
    {
        return new Attribute(
            'description',
            $attributeType,
            [],
            false,
            false,
            null,
            null,
            null,
            'date',
            []
        );
    }
}
