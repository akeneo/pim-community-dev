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

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Consistency\TextChecker\Source;

use PhpSpec\ObjectBehavior;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class GlobalOffsetCalculatorSpec extends ObjectBehavior
{
    public function it_computes_global_offset()
    {
        $this->compute("ABCDE\nFGHI\nJK", -1, -1)->shouldBe(0);
        $this->compute("ABCDE\nFGHI\nJK", -1, 10)->shouldBe(0);
        $this->compute("ABCDE\nFGHI\nJK", 1, 0)->shouldBe(0);
        $this->compute("ABCDE\nFGHI\nJK", 1, 2)->shouldBe(2);
        $this->compute("ABCDE\nFGHI\nJK", 2, 2)->shouldBe(8);
        $this->compute("ABCDE\nFGHI\nJK", 4, 1)->shouldBe(13);
    }
}
