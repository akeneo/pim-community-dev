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

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeLabel;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class AttributeLabelSpec extends ObjectBehavior
{
    public function it_it_initializable(): void
    {
        $this->beConstructedWith('foo');
        $this->shouldHaveType(AttributeLabel::class);
    }

    public function it_throws_an_exception_when_label_is_empty(): void
    {
        $this->beConstructedWith('');
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_returns_the_label(): void
    {
        $this->beConstructedWith('foobar');
        $this->__toString()->shouldReturn('foobar');
    }
}
