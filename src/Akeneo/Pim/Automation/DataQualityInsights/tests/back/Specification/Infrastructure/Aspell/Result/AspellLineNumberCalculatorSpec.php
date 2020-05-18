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
class AspellLineNumberCalculatorSpec extends ObjectBehavior
{
    public function it_computes_line_number()
    {
        $this->compute("ABCDE\nFGHI\nJK", -1, -1, 'ABCDE')->shouldBe(0);
        $this->compute("ABCDE\nFGHI\nJK", 1, 0, 'ABCDE')->shouldBe(1);
        $this->compute("ABCDE\nFGHI\nJK", 2, 0, 'FGHI')->shouldBe(2);
        $this->compute("ABCDE\n\nFGHI\nJK", 2, 0, 'FGHI')->shouldBe(3);
        $this->compute("ABCDE\n\nFGHI\nJK", 3, 0, 'JK')->shouldBe(4);
        $this->compute("ABCDE\n\n\nFGHI\n\nJK", 3, 0, 'JK')->shouldBe(6);
    }

    public function it_computes_line_number_with_multilines_and_unicode_characters()
    {
        $example = " READING MATERIAL.\n\nKick back with the 4:3 aspect ratio screen. It offers pleasant reading that's easy on your eyes and comfortable in yourhand. \n\n THE BIG PICTURE. \n\nGet more out of your screen with its 4:3 aspect ratio. When browsing the web, see more content in one scroll. \n\n SOLIDLY SVELTE. \n\nAn elegantly slender from encased in quality metal. The Galaxy Tab S2 feels just right in your hand. \n\n LIGHTWEIGHT.\n\n Weighing less than ever before, the Galaxy Tab S2 ensures ultimate portability and hors of reaing, wathing and surfing in comfort.";
        $this->compute($example, 2, 115, 'yourhand')->shouldBe(3);
        $this->compute($example, 8, 84, 'hors')->shouldBe(15);
        $this->compute($example, 8, 92, 'reaing')->shouldBe(15);

        // This example contains some unicode characters
        $example2 = "A Tablet With a Unique Modular Design...\nWe call it the ThinkPad X1 Tablet, but it's so much more than your average 2-in-1 hybrid. The unique modular design lets you turn the X1 Tablet into anything from a portable entertainment center, to a creativity device, or a productivity laptop.\n\nWith the Power of a PC\nThe 12-inch X1 Tablet is perfect for on-the-go, weighing less than 800 g / 1.7 lbs. It delivers a powerful PC experience with an Intel® Core™ processor, Windows 10 Pro OS, optional 4G LTE-A mobile broadband, and up to 10 hours of battery life*.\n";
        $this->compute($example2, 2, 15, 'ThinkPad')->shouldBe(2);
        $this->compute($example2, 4, 184, 'LTE')->shouldBe(5);

        $example3 = "typos hapen\n\ntypos hapen\n\ntypos hapen\n\ntypos hapen";
        $this->compute($example3, 1, 6, 'hapen')->shouldBe(1);
        $this->compute($example3, 2, 6, 'hapen')->shouldBe(3);
        $this->compute($example3, 3, 6, 'hapen')->shouldBe(5);
        $this->compute($example3, 4, 6, 'hapen')->shouldBe(7);
    }

    public function it_computes_when_word_does_not_exist()
    {
        $this->compute("ABCDE\n\nFGHI\nJK", 2, 0, 'XXXXX')->shouldBe(2);
    }
}
