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

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Aspell\Result;

use PhpSpec\ObjectBehavior;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class AspellGlobalOffsetCalculatorSpec extends ObjectBehavior
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

    public function it_computes_global_offset_with_multilines_and_unicode_characters()
    {
        $example2 = "A Tablet With a Unique Modular Design...\nWe call it the ThinkPad X1 Tablet, but it's so much more than your average 2-in-1 hybrid. The unique modular design lets you turn the X1 Tablet into anything from a portable entertainment center, to a creativity device, or a productivity laptop.\n\nWith the Power of a PC\nThe 12-inch X1 Tablet is perfect for on-the-go, weighing less than 800 g / 1.7 lbs. It delivers a powerful PC experience with an Intel® Core™ processor, Windows 10 Pro OS, optional 4G LTE-A mobile broadband, and up to 10 hours of battery life*.\n";
        $this->compute($example2, 2, 15)->shouldBe(56); // ThinkPad
        $this->compute($example2, 5, 184)->shouldBe(495); // LTE
    }
}
