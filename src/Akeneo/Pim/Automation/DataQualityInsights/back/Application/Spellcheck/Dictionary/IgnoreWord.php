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

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\Spellcheck\Dictionary;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Spellcheck\SupportedLocaleValidator;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\TextCheckerDictionaryWord;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\TextCheckerDictionaryRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DictionaryWord;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;

class IgnoreWord
{
    /** @var SupportedLocaleValidator */
    private $supportedLocaleValidator;

    /** @var TextCheckerDictionaryRepositoryInterface */
    private $textCheckerDictionaryRepository;

    public function __construct(SupportedLocaleValidator $supportedLocaleValidator, TextCheckerDictionaryRepositoryInterface $textCheckerDictionaryRepository)
    {
        $this->supportedLocaleValidator = $supportedLocaleValidator;
        $this->textCheckerDictionaryRepository = $textCheckerDictionaryRepository;
    }

    public function execute(DictionaryWord $word, LocaleCode $localeCode): void
    {
        if (!$this->supportedLocaleValidator->isSupported($localeCode)) {
            throw new \InvalidArgumentException('Unable to process locales that are not handled by spellchecker');
        }

        $dictionaryWord = new TextCheckerDictionaryWord($localeCode, $word);
        $this->textCheckerDictionaryRepository->save($dictionaryWord);
    }
}
