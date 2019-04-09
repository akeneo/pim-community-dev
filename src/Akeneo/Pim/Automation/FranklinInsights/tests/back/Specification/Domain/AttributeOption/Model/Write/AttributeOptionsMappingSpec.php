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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Model\Write;

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Model\Write\AttributeOption;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Model\Write\AttributeOptionsMapping;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class AttributeOptionsMappingSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(new AttributeCode('color'));
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(AttributeOptionsMapping::class);
    }

    public function it_is_iterable(): void
    {
        $this->shouldImplement(\IteratorAggregate::class);
    }

    public function it_gets_option_codes(): void
    {
        $this->addAttributeOption(new AttributeOption('color1', 'red', 'pim_color_1'));
        $this->addAttributeOption(new AttributeOption('color2', 'blue', 'pim_color_2'));

        $this->getOptionCodes()->shouldReturn(['pim_color_1', 'pim_color_2']);
    }

    public function it_returns_an_empty_array_when_no_options(): void
    {
        $this->getOptionCodes()->shouldReturn([]);
    }
}
