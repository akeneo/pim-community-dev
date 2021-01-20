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

    public function __construct(TextCheckerDictionaryRepositoryInterface $dictionaryRepository)
    {
        $this->dictionaryRepository = $dictionaryRepository;
    }

    /**
     * @AclAncestor("pim_enrich_locale_index")
     */
    public function add(Request $request, string $localeCode)
    {
        $localeCode = new LocaleCode($localeCode);
        $words = json_decode($request->getContent(), true);

        $this->dictionaryRepository->saveAll(
            array_map(fn ($word) => new TextCheckerDictionaryWord($localeCode, new DictionaryWord($word)), $words)
        );

        return new JsonResponse(null, JsonResponse::HTTP_CREATED);
    }
}
