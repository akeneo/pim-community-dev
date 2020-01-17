<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DictionaryWord;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class TextCheckerDictionaryWord
{
    /**
     * @var LocaleCode
     */
    private $localeCode;

    /**
     * @var DictionaryWord
     */
    private $word;

    public function __construct(LocaleCode $localeCode, DictionaryWord $word)
    {
        $this->localeCode = $localeCode;
        $this->word = $word;
    }

    public function getLocaleCode(): LocaleCode
    {
        return $this->localeCode;
    }

    public function getWord(): DictionaryWord
    {
        return $this->word;
    }

    public function __toString()
    {
        return strval($this->getWord());
    }
}
