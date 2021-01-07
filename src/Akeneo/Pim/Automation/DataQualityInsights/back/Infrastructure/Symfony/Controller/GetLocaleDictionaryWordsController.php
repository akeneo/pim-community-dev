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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\TextCheckerDictionaryWord;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\TextCheckerDictionaryRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

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
        $itemsPerPage = $request->query->getInt('itemsPerPage', 25);

        $localeCode = new LocaleCode($localeCode);
        $words = $this->dictionaryRepository->findByLocaleCode($localeCode);

        return new JsonResponse(
            [
                'results' =>
                    iterator_to_array(
                        new \LimitIterator(
                            new \ArrayIterator(
                                array_map(fn (TextCheckerDictionaryWord $word) => ['id' => rand(1, 9999), 'label' => (string) $word], $words)
                            ),
                            max(($page - 1) * $itemsPerPage, 0),
                            $itemsPerPage
                        )
                    ),
                'total' => count($words),
            ]
        );
    }
}
