<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FranklinAttributeLabel;
use PhpSpec\ObjectBehavior;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class FranklinAttributeLabelSpec extends ObjectBehavior
{
    public function it_is_a_franklin_attribute_label(): void
    {
        $this->beConstructedWith('foo');
        $this->shouldBeAnInstanceOf(FranklinAttributeLabel::class);
    }

    public function it_throws_an_exception_when_attribute_id_is_empty(): void
    {
        $this->beConstructedWith('');
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_returns_the_attribute_label(): void
    {
        $this->beConstructedWith('foo');
        $this->__toString()->shouldReturn('foo');
    }
}
