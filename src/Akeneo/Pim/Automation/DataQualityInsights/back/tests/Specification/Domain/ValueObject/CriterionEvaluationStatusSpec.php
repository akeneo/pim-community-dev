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

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject;

use PhpSpec\ObjectBehavior;

class CriterionEvaluationStatusSpec extends ObjectBehavior
{
    public function it_throws_an_exception_if_constructed_with_an_empty_string()
    {
        $this->beConstructedWith('');
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_throws_an_exception_if_constructed_with_an_unknown_status()
    {
        $this->beConstructedWith('Foo');
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_can_be_a_pending_status()
    {
        $this->beConstructedThrough('pending');
        $this->isPending()->shouldBe(true);
    }
}
