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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\TextCheckerDictionaryRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Webmozart\Assert\Assert;

final class GetLocaleDictionaryWordsController
{
    private TextCheckerDictionaryRepositoryInterface $dictionaryRepository;

    public function __construct(TextCheckerDictionaryRepositoryInterface $dictionaryRepository)
    {
        $this->dictionaryRepository = $dictionaryRepository;
    }

    public function __invoke(Request $request, string $localeCode)
    {
        $page = $request->query->getInt('page', 1);
        Assert::greaterThanEq($page, 1, 'The page parameter must be greater than 1');
        $itemsPerPage = $request->query->getInt('itemsPerPage', 25);
        Assert::range($itemsPerPage, 1, 100, 'The number of items per page must be between 1 and 100');
        $search = $request->query->getAlnum('search', "");

        $localeCode = new LocaleCode($localeCode);

        $result = $this->dictionaryRepository->paginatedSearch($localeCode, $page, $itemsPerPage, $search);

        return new JsonResponse($result);
    }
}
