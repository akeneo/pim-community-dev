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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Dictionary\GetNumberOfWordsQueryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GetLocalesDictionaryInfoController
{
    private GetNumberOfWordsQueryInterface $getNumberOfWordsQuery;

    public function __construct(GetNumberOfWordsQueryInterface $getNumberOfWordsQuery)
    {
        $this->getNumberOfWordsQuery = $getNumberOfWordsQuery;
    }

    public function __invoke(Request $request): Response
    {
        $locales = $request->get('locales', []);
        $numberOfWords = $this->getNumberOfWordsQuery->byLocales($locales);

        $infos = [];
        foreach ($locales as $locale) {
            $infos[$locale] = $numberOfWords[$locale] ?? 0;
        }

        return new JsonResponse($infos);
    }
}
