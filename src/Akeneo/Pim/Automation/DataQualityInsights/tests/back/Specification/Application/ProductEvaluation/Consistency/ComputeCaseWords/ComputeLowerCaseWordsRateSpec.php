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

final class ComputeLowerCaseWordsRateSpec extends ObjectBehavior
{
    public function it_sets_no_rate()
    {
        ($this('<div></div>'))->shouldBeLike(null);
        ($this('      '))->shouldBeLike(null);
    }

    public function it_sets_a_rate_from_a_string_value()
    {
        ($this('<div>Text HTML without error.</div>'))->shouldBeLike(new Rate(100));
        ($this('There is: one error'))->shouldBeLike(new Rate(76));
        ($this('<p>there is: two errors</p>'))->shouldBeLike(new Rate(52));
        ($this('is there: three errors? yes.'))->shouldBeLike(new Rate(28));
        ($this('four errors. is worst! than three? indeed.'))->shouldBeLike(new Rate(4));
        ($this('five: errors. are? too: much!'))->shouldBeLike(new Rate(0));
        ($this('Text without error.'))->shouldBeLike(new Rate(100));
        ($this('Peu importe'))->shouldBeLike(new Rate(100));
    }
}
