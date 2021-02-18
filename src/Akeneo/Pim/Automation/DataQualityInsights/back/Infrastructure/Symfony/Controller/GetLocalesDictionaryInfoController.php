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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Controller;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Spellcheck\SupportedLocaleValidator;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Dictionary\GetNumberOfWordsQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GetLocalesDictionaryInfoController
{
    private GetNumberOfWordsQueryInterface $getNumberOfWordsQuery;

    private SupportedLocaleValidator $supportedLocaleValidator;

    public function __construct(
        GetNumberOfWordsQueryInterface $getNumberOfWordsQuery,
        SupportedLocaleValidator $supportedLocaleValidator
    ) {
        $this->getNumberOfWordsQuery = $getNumberOfWordsQuery;
        $this->supportedLocaleValidator = $supportedLocaleValidator;
    }

    public function __invoke(Request $request): Response
    {
        $locales = $request->get('locales', []);
        $numberOfWords = $this->getNumberOfWordsQuery->byLocales($locales);

        $infos = [];
        foreach ($locales as $locale) {
            if (!$this->supportedLocaleValidator->isSupported(new LocaleCode($locale))) {
                $infos[$locale] = null;
                continue;
            }

            $infos[$locale] = $numberOfWords[$locale] ?? 0;
        }

        return new JsonResponse($infos);
    }
}
