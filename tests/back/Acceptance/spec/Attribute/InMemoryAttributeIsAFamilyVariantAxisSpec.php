<?php

declare(strict_types=1);

namespace spec\Akeneo\Test\Acceptance\Attribute;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute\AttributeIsAFamilyVariantAxisInterface;
use Akeneo\Test\Acceptance\Attribute\InMemoryAttributeIsAFamilyVariantAxis;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class InMemoryAttributeIsAFamilyVariantAxisSpec extends ObjectBehavior
{
    public function it_is_a_query_to_check_attribute_is_a_family_variant_axis()
    {
        $this->shouldImplement(AttributeIsAFamilyVariantAxisInterface::class);
    }

    public function it_is_an_in_memory_query()
    {
        $this->shouldBeAnInstanceOf(InMemoryAttributeIsAFamilyVariantAxis::class);
    }

    public function it_returns_false_when_memory_is_empty()
    {
        $this->execute('someAttributeCode')->shouldReturn(false);
    }

    public function it_returns_in_memory_values_for_family_variant_axis()
    {
        $this->setAxisAttribute('attributeA', false);
        $this->setAxisAttribute('attributeB', true);

        $this->execute('attributeA')->shouldReturn(false);
        $this->execute('attributeB')->shouldReturn(true);
        $this->execute('attributeC')->shouldReturn(false);
    }
}
