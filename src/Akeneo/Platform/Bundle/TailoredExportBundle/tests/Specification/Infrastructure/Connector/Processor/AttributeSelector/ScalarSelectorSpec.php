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

class ScalarSelectorSpec extends ObjectBehavior
{
    public function it_returns_attribute_type_supported()
    {
        $this->beConstructedWith(['pim_catalog_boolean', 'pim_catalog_text']);

        $booleanAttribute = $this->createAttribute('pim_catalog_boolean');
        $this->supports(['type' => 'code'], $booleanAttribute)->shouldReturn(true);

        $textAreaAttribute = $this->createAttribute('pim_catalog_text_area');
        $this->supports(['type' => 'code'], $textAreaAttribute)->shouldReturn(false);
    }

    public function it_selects_the_data(ValueInterface $value)
    {
        $this->beConstructedWith(['pim_catalog_boolean', 'pim_catalog_text']);
        $booleanAttribute = $this->createAttribute('pim_catalog_boolean');
        $value->getData()->willReturn('The value');

        $this->applySelection(['type' => 'code'], $booleanAttribute, $value)->shouldReturn('The value');
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
            'bool',
            []
        );
    }
}
