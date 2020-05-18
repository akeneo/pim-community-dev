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

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Aspell;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Exception\UnableToRetrieveDictionaryException;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LanguageCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Aspell\AspellDictionaryInterface;
use Mekras\Speller;
use Mekras\Speller\Aspell\Aspell;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;

final class AspellSpellerProviderSpec extends ObjectBehavior
{
    public function let(AspellDictionaryInterface $aspellDictionary, LoggerInterface $logger)
    {
        $this->beConstructedWith($aspellDictionary, $logger, '/binary/path');
    }

    public function it_provides_a_speller_with_dictionary(AspellDictionaryInterface $aspellDictionary)
    {
        $locale = new LocaleCode('en_US');

        $dictionary = new Speller\Dictionary('/an/absolute/filepath-en.pw');
        $aspellDictionary->getUpToDateSpellerDictionary($locale)->willReturn($dictionary);

        $expectedSpeller = new Aspell('/binary/path');
        $expectedSpeller->setPersonalDictionary($dictionary);

        $this->getByLocale($locale)->shouldBeLike($expectedSpeller);
    }

    public function it_provides_a_speller_without_dictionary_if_there_is_none(AspellDictionaryInterface $aspellDictionary)
    {
        $locale = new LocaleCode('en_US');

        $aspellDictionary->getUpToDateSpellerDictionary($locale)->willReturn(null);

        $this->getByLocale($locale)->shouldBeLike(new Aspell('/binary/path'));
    }

    public function it_provides_a_speller_even_if_retrieving_the_dictionary_failed(AspellDictionaryInterface $aspellDictionary)
    {
        $locale = new LocaleCode('en_US');

        $aspellDictionary->getUpToDateSpellerDictionary($locale)->willThrow(new UnableToRetrieveDictionaryException(new LanguageCode('en')));

        $this->getByLocale($locale)->shouldBeLike(new Aspell('/binary/path'));
    }
}
