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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read;

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read\AttributeMapping;
use PhpSpec\ObjectBehavior;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class AttributeMappingSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(
            'series',
            'Series',
            'text',
            'pim_series',
            AttributeMapping::ATTRIBUTE_MAPPED,
            ['Hair care']
        );
    }

    public function it_is_an_attribute_mapping(): void
    {
        $this->shouldHaveType(AttributeMapping::class);
    }

    public function it_exposes_its_properties(): void
    {
        $this->getTargetAttributeCode()->shouldReturn('series');
        $this->getTargetAttributeLabel()->shouldReturn('Series');
        $this->getTargetAttributeType()->shouldReturn('text');
        $this->getPimAttributeCode()->shouldReturn('pim_series');
        $this->getStatus()->shouldReturn(AttributeMapping::ATTRIBUTE_MAPPED);
        $this->getSummary()->shouldReturn(['Hair care']);
    }

    public function it_can_have_null_target_label_and_summary(): void
    {
        $this->beConstructedWith(
            'series',
            null,
            'text',
            'pim_series',
            AttributeMapping::ATTRIBUTE_MAPPED,
            null
        );
        $this->getTargetAttributeLabel()->shouldReturn(null);
        $this->getSummary()->shouldReturn(null);
    }
}
