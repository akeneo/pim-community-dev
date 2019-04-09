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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Model\Read;

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Model\Read\AttributeOptionMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Model\Read\AttributeOptionsMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeOptionCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use PhpSpec\ObjectBehavior;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class AttributeOptionsMappingSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(new FamilyCode('router'), 'color', [
            new AttributeOptionMapping('red', 'red', AttributeOptionMapping::STATUS_PENDING, new AttributeOptionCode('pim_red')),
            new AttributeOptionMapping('blue', 'blue', AttributeOptionMapping::STATUS_PENDING, new AttributeOptionCode('pim_blue')),
            new AttributeOptionMapping('black', 'black', AttributeOptionMapping::STATUS_PENDING, new AttributeOptionCode('pim_black')),
            new AttributeOptionMapping('yellow', 'yellow', AttributeOptionMapping::STATUS_PENDING, null),
        ]);
    }

    public function it_is_an_attribute_options_mapping(): void
    {
        $this->shouldHaveType(AttributeOptionsMapping::class);
    }

    public function it_can_check_if_attribute_exists(): void
    {
        $this->hasPimAttributeOption(new AttributeOptionCode('pim_blue'))->shouldReturn(true);
        $this->hasPimAttributeOption(new AttributeOptionCode('unknown'))->shouldReturn(false);
        $this->hasPimAttributeOption(new AttributeOptionCode('pim_red'))->shouldReturn(true);
        $this->hasPimAttributeOption(new AttributeOptionCode('yellow'))->shouldReturn(false);
    }

    public function it_sorts_alphabetically(): void
    {
        $colorRed = new AttributeOptionMapping('red', 'red', AttributeOptionMapping::STATUS_PENDING, new AttributeOptionCode('pim_red'));
        $colorBlue = new AttributeOptionMapping('blue', 'blue', AttributeOptionMapping::STATUS_PENDING, new AttributeOptionCode('pim_blue'));
        $colorBlack = new AttributeOptionMapping('black', 'black', AttributeOptionMapping::STATUS_PENDING, new AttributeOptionCode('pim_black'));

        $this->beConstructedWith(new FamilyCode('router'), 'color', [$colorRed, $colorBlue, $colorBlack]);

        $this->mapping()->shouldReturn([$colorBlack, $colorBlue, $colorRed]);
    }
}
