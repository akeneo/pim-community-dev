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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Domain\AttributeOption\Model\Read;

use Akeneo\Pim\Automation\SuggestData\Domain\AttributeOption\Model\Read\AttributeOptionMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\AttributeOption\Model\Read\AttributeOptionsMapping;
use PhpSpec\ObjectBehavior;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class AttributeOptionsMappingSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith('router', 'color', [
            new AttributeOptionMapping('red', 'red', AttributeOptionMapping::STATUS_PENDING, 'pim_red'),
            new AttributeOptionMapping('blue', 'blue', AttributeOptionMapping::STATUS_PENDING, 'pim_blue'),
            new AttributeOptionMapping('black', 'black', AttributeOptionMapping::STATUS_PENDING, 'pim_black'),
        ]);
    }

    public function it_is_an_attribute_options_mapping(): void
    {
        $this->shouldHaveType(AttributeOptionsMapping::class);
    }

    public function it_is_traversable(): void
    {
        $this->shouldHaveType(\Traversable::class);

        $this->getIterator()->shouldReturnAnInstanceOf(\Iterator::class);
    }

    public function it_can_check_if_attribute_exists(): void
    {
        $this->hasPimAttributeOption('pim_blue')->shouldReturn(true);
        $this->hasPimAttributeOption('unknown')->shouldReturn(false);
        $this->hasPimAttributeOption('pim_red')->shouldReturn(true);
    }
}
