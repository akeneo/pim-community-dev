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

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\AttributeMappingStatus;
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
            AttributeMappingStatus::ATTRIBUTE_ACTIVE,
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
        $this->getStatus()->shouldReturn(AttributeMappingStatus::ATTRIBUTE_ACTIVE);
        $this->getSummary()->shouldReturn(['Hair care']);
        $this->isMapped()->shouldReturn(true);
        $this->isPending()->shouldReturn(false);
        $this->getExactMatchAttributeFromOtherFamily()->shouldReturn(null);
        $this->canCreateAttribute()->shouldReturn(false);
    }

    public function it_can_have_null_target_label_and_summary(): void
    {
        $this->beConstructedWith(
            'series',
            null,
            'text',
            'pim_series',
            AttributeMappingStatus::ATTRIBUTE_ACTIVE,
            null
        );
        $this->getTargetAttributeLabel()->shouldReturn(null);
        $this->getSummary()->shouldReturn(null);
    }

    public function it_is_mapped_when_the_attribute_is_active(): void
    {
        $this->beConstructedWith(
            'series',
            null,
            'text',
            'pim_series',
            AttributeMappingStatus::ATTRIBUTE_ACTIVE,
            null
        );
        $this->isMapped()->shouldReturn(true);
    }

    public function it_is_not_mapped_when_the_attribute_is_inactive(): void
    {
        $this->beConstructedWith(
            'series',
            null,
            'text',
            'pim_series',
            AttributeMappingStatus::ATTRIBUTE_INACTIVE,
            null
        );
        $this->isMapped()->shouldReturn(false);
    }

    public function it_is_not_mapped_when_the_attribute_is_pending(): void
    {
        $this->beConstructedWith(
            'series',
            null,
            'text',
            'pim_series',
            AttributeMappingStatus::ATTRIBUTE_PENDING,
            null
        );
        $this->isMapped()->shouldReturn(false);
    }

    public function it_can_create_attribute_from_franklin(): void
    {
        $this->beConstructedWith(
            'series',
            'Series',
            'text',
            null,
            AttributeMappingStatus::ATTRIBUTE_PENDING,
            null,
            null
        );

        $this->canCreateAttribute()->shouldReturn(true);
    }

    public function it_has_exact_match_from_other_family(): void
    {
        $this->beConstructedWith(
            'series',
            'Series',
            'text',
            null,
            AttributeMappingStatus::ATTRIBUTE_PENDING,
            null,
            'pim_series'
        );

        $this->canCreateAttribute()->shouldReturn(false);
        $this->getExactMatchAttributeFromOtherFamily()->shouldReturn('pim_series');
    }
}
