<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Infrastructure\Controller;

use Akeneo\ReferenceEntity\Infrastructure\PublicApi\Platform\SearchRecordsInterface;
use Akeneo\ReferenceEntity\Infrastructure\PublicApi\Platform\SearchRecordsParameters;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class GetRecordsAction
{
    private const LIMIT_DEFAULT = 25;

    private SearchRecordsInterface $searchRecords;

    public function __construct(
        SearchRecordsInterface $searchRecords
    ) {
        $this->searchRecords = $searchRecords;
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $referenceEntityCode = $request->get('reference_entity_code', null);

        if (null === $referenceEntityCode) {
            throw new BadRequestHttpException('Missing reference entity code');
        }

        $searchRecordsParameters = new SearchRecordsParameters();
        $searchRecordsParameters->setIncludeCodes($request->get('include_codes', []));
        $searchRecordsParameters->setExcludeCodes($request->get('exclude_codes', []));
        $searchRecordsParameters->setSearch($request->get('search', null));
        $searchRecordsParameters->setLocale($request->get('locale', null));
        $searchRecordsParameters->setLimit($request->get('limit', self::LIMIT_DEFAULT));
        $searchRecordsParameters->setPage($request->get('page', null));

        $searchRecordsResult = $this->searchRecords->search(
            $referenceEntityCode,
            $searchRecordsParameters,
        );

        return new JsonResponse($searchRecordsResult->normalize());
    }
}
