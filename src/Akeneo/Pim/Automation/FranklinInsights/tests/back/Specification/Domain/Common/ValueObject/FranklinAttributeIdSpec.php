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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FranklinAttributeId;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class FranklinAttributeIdSpec extends ObjectBehavior
{
    public function it_is_a_franklin_attribute_id(): void
    {
        $this->beConstructedWith('foo');
        $this->shouldBeAnInstanceOf(FranklinAttributeId::class);
    }

    public function it_throws_an_exception_when_attribute_id_is_empty(): void
    {
        $this->beConstructedWith('');
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_returns_the_attribute_id(): void
    {
        $this->beConstructedWith('foo');
        $this->__toString()->shouldReturn('foo');
    }
}
