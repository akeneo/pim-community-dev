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
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class DeleteWordFromDictionaryController
{
    private TextCheckerDictionaryRepositoryInterface $dictionaryRepository;

    public function __construct(TextCheckerDictionaryRepositoryInterface $dictionaryRepository)
    {
        $this->dictionaryRepository = $dictionaryRepository;
    }

    /**
     * @AclAncestor("pim_enrich_locale_index")
     */
    public function delete(Request $request, string $wordId)
    {
        $this->dictionaryRepository->deleteWord((int) $wordId);

        return new JsonResponse(null, JsonResponse::HTTP_OK);
    }
}
