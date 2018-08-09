<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\SuggestData\Domain\Model;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class IdentifierMappingSpec extends ObjectBehavior
{
    function let(AttributeInterface $akeneoAttribute)
    {
        $this->beConstructedWith('pim_ai_code', $akeneoAttribute);
    }

    function it_gets_pim_ai_attribute_code()
    {
        $this->getPimAiCode()->shouldReturn('pim_ai_code');
    }

    function it_gets_akeneo_attribute($akeneoAttribute)
    {
        $this->getAttribute()->shouldReturn($akeneoAttribute);
    }

    function it_sets_an_akeneo_attribute($akeneoAttribute, AttributeInterface $anotherAkeneoAttribute)
    {
        $this->getAttribute()->shouldReturn($akeneoAttribute);

        $this->setAttribute($anotherAkeneoAttribute);

        $this->getAttribute()->shouldReturn($anotherAkeneoAttribute);
    }

    function it_sets_an_akeneo_attribute_to_null($akeneoAttribute)
    {
        $this->getAttribute()->shouldReturn($akeneoAttribute);

        $this->setAttribute(null);

        $this->getAttribute()->shouldReturn(null);
    }

    function it_build_an_identifier_mapping_object_without_akeneo_attribute()
    {
        $this->beConstructedWith('pim_ai_code', null);

        $this->getPimAiCode()->shouldReturn('pim_ai_code');
        $this->getAttribute()->shouldReturn(null);
    }
}
