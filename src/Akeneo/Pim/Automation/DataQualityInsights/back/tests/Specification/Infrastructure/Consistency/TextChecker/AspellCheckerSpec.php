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

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Consistency\TextChecker;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\TextCheckResultCollection;
use Mekras\Speller\Aspell\Aspell;
use Mekras\Speller\Issue;
use PhpSpec\ObjectBehavior;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class AspellCheckerSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('aspell');
    }

    public function it_checks_test(
        Aspell $speller
    ) {
        $text = 'Typos hapen.';
        $locale = 'en_US';

        $aspellCheckResult = [
            new Issue('hapen', 'ANY_ASPELL_RETURN_CODE'),
        ];

        $speller->checkText($text, [$locale])->willReturn($aspellCheckResult);

        $result = $this->check($text, $locale);

        $result->shouldBeAnInstanceOf(TextCheckResultCollection::class);
        $result->count()->shouldBe(1);
    }

    public function it_checks_test_without_issue(
        Aspell $speller
    ) {
        $text = 'Typos happen.';
        $locale = 'en_US';

        $aspellCheckResult = [];

        $speller->checkText($text, [$locale])->willReturn($aspellCheckResult);

        $result = $this->check($text, $locale);

        $result->shouldBeAnInstanceOf(TextCheckResultCollection::class);
        $result->count()->shouldBe(0);
    }
}
