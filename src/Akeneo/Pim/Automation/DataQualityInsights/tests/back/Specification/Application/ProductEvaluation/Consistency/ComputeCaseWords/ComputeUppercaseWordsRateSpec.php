<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Consistency\ComputeCaseWords;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use PhpSpec\ObjectBehavior;

final class ComputeUppercaseWordsRateSpec extends ObjectBehavior
{
    public function it_sets_no_rate()
    {
        ($this('<div></div>'))->shouldBeLike(null);
        ($this('      '))->shouldBeLike(null);
    }

    public function it_sets_a_rate_from_a_string_value()
    {
        ($this('TEXTAREA1 TEXT'))->shouldBeLike(new Rate(0));
        ($this('<STRONG>Textarea2 mobile fr_fr</STRONG>'))->shouldBeLike(new Rate(100));
        ($this('<STRONG>TEXTAREA2 MOBILE EN_US</STRONG>'))->shouldBeLike(new Rate(0));
        ($this('12-23'))->shouldBeLike(new Rate(100));
        ($this('Peu importe'))->shouldBeLike(new Rate(100));
    }
}
