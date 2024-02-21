<?php

declare(strict_types=1);

namespace spec\Akeneo\Test\Acceptance\Attribute;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Permission\IsAttributeEditable;
use Akeneo\Test\Acceptance\Attribute\InMemoryIsAttributeEditable;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class InMemoryIsAttributeEditableSpec extends ObjectBehavior
{
    function let()
    {
        $this->addNotEditableAttributeForUser('a_text', 4);
        $this->addNotEditableAttributeForUser('a_textarea', 6);
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(InMemoryIsAttributeEditable::class);
        $this->shouldImplement(IsAttributeEditable::class);
    }

    function it_returns_if_the_attribute_is_editable_or_not()
    {
        $this->forCode('a_text', 4)->shouldReturn(false);
        $this->forCode('a_textarea', 4)->shouldReturn(true);
        $this->forCode('other', 4)->shouldReturn(true);

        $this->forCode('a_text', 6)->shouldReturn(true);
        $this->forCode('a_textarea', 6)->shouldReturn(false);
        $this->forCode('other', 6)->shouldReturn(true);

        $this->forCode('a_text', 99)->shouldReturn(true);
        $this->forCode('a_textarea', 99)->shouldReturn(true);
        $this->forCode('other', 99)->shouldReturn(true);
    }
}
