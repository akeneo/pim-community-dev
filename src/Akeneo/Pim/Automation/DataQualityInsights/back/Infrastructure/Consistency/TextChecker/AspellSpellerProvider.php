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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Consistency\TextChecker;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Exception\UnableToRetrieveDictionaryException;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Mekras\Speller\Aspell\Aspell;
use Mekras\Speller\Speller;
use Psr\Log\LoggerInterface;

final class AspellSpellerProvider implements SpellerProviderInterface
{
    /** @var string */
    private $binaryPath;

    /** @var array */
    private $spellers;

    /** @var AspellDictionaryInterface */
    private $aspellDictionary;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(AspellDictionaryInterface $aspellDictionary, LoggerInterface $logger, string $binaryPath)
    {
        $this->aspellDictionary = $aspellDictionary;
        $this->binaryPath = $binaryPath;
        $this->spellers = [];
        $this->logger = $logger;
    }

    public function getByLocale(LocaleCode $localeCode): Speller
    {
        $locale = strval($localeCode);
        if (!isset($this->spellers[$locale])) {
            $this->spellers[$locale] = $this->buildSpeller($localeCode);
        }

        return $this->spellers[$locale];
    }

    private function buildSpeller(LocaleCode $localeCode): Speller
    {
        $speller = new Aspell($this->binaryPath);

        try {
            $dictionary = $this->aspellDictionary->getUpToDateSpellerDictionary($localeCode);
            if (null !== $dictionary) {
                $speller->setPersonalDictionary($dictionary);
            }
        } catch (UnableToRetrieveDictionaryException $exception) {
            $this->logger->warning('An error occurred during building Aspell speller', ['error_code' => 'error_during_building_speller', 'error_message' => $exception->getMessage()]);
        }

        return $speller;
    }
}
