<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Infrastructure\Controller;

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

    public function __construct(
        private SearchRecordsInterface $searchRecords,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $referenceEntityCode = $request->get('reference_entity_code');
        if (null === $referenceEntityCode) {
            throw new BadRequestHttpException('Missing reference entity code');
        }

        $channel = $request->get('channel');
        if (null === $channel) {
            throw new BadRequestHttpException('Missing channel');
        }

        $locale = $request->get('locale');
        if (null === $locale) {
            throw new BadRequestHttpException('Missing locale');
        }

        $searchRecordsParameters = new SearchRecordsParameters();
        $searchRecordsParameters->setLimit($request->get('limit', self::LIMIT_DEFAULT));
        $searchRecordsParameters->setPage($request->get('page', 1));

        $searchRecordsResult = $this->searchRecords->search(
            $referenceEntityCode,
            $channel,
            $locale,
            $searchRecordsParameters,
        );

        return new JsonResponse($searchRecordsResult->normalize());
    }
}
