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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Spellcheck;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Spellcheck\TextChecker;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Exception\TextCheckFailedException;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\TextCheckResultCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Spellcheck\Source\TextSource;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class Checker implements TextChecker
{
    public function __construct(
        private SpellerInterface $spellerProvider,
    ) {
    }

    /**
     * @throws TextCheckFailedException
     */
    public function check(string $text, LocaleCode $locale): TextCheckResultCollection
    {
        return $this->spellerProvider->check(new TextSource($text), $locale);
    }
}
