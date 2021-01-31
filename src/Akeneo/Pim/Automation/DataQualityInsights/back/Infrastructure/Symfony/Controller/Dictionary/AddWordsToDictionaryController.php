<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Controller\Dictionary;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Spellcheck\SupportedLocaleValidator;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\TextCheckerDictionaryWord;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\TextCheckerDictionaryRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DictionaryWord;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class AddWordsToDictionaryController
{
    private TextCheckerDictionaryRepositoryInterface $dictionaryRepository;

    private SupportedLocaleValidator $supportedLocaleValidator;

    public function __construct(
        TextCheckerDictionaryRepositoryInterface $dictionaryRepository,
        SupportedLocaleValidator $supportedLocaleValidator
    ) {
        $this->dictionaryRepository = $dictionaryRepository;
        $this->supportedLocaleValidator = $supportedLocaleValidator;
    }

    /**
     * @AclAncestor("pim_enrich_locale_index")
     */
    public function addWordsToSingleLocale(Request $request, string $localeCode)
    {
        $localeCode = new LocaleCode($localeCode);
        $words = $this->getWordsToAddToDictionary(json_decode($request->getContent(), true), $localeCode);

        $this->dictionaryRepository->saveAll($words);

        return new JsonResponse(null, JsonResponse::HTTP_CREATED);
    }

    /**
     * @AclAncestor("pim_enrich_locale_index")
     */
    public function addWordsToMultipleLocales(Request $request)
    {
        $requestContent = json_decode($request->getContent(), true);
        $locales = $requestContent['locales'] ?? [];
        $words = $requestContent['words'] ?? [];

        $supportedLocaleCodes = [];
        foreach ($this->supportedLocaleValidator->getSupportedLocaleCollection() as $languageCode => $localeCollection) {
            $supportedLocaleCodes = array_merge($supportedLocaleCodes, $localeCollection->toArrayString());
        }

        $wordsToAdd = [];
        foreach ($locales as $locale) {
            if (!in_array($locale, $supportedLocaleCodes)) {
                continue;
            }
            $localeCode = new LocaleCode(trim($locale));
            $wordsToAdd = array_merge($wordsToAdd, $this->getWordsToAddToDictionary($words, $localeCode));
        }

        $this->dictionaryRepository->saveAll($wordsToAdd);

        return new JsonResponse(null, JsonResponse::HTTP_CREATED);
    }

    private function getWordsToAddToDictionary($requestWords, LocaleCode $localeCode): array
    {
        $words = [];
        foreach ($requestWords as $word) {
            try {
                $words[] = new TextCheckerDictionaryWord($localeCode, new DictionaryWord($word));
            } catch (\InvalidArgumentException $e) {
            }
        }

        return $words;
    }
}
