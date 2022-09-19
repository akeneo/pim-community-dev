<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\TailoredImport\Infrastructure\Controller;

use Akeneo\ReferenceEntity\Infrastructure\PublicApi\Platform\SearchRecordsInterface;
use Akeneo\ReferenceEntity\Infrastructure\PublicApi\Platform\SearchRecordsParameters;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class GetRecordsAction
{
    private const LIMIT_DEFAULT = 25;

    public function __construct(
        private SearchRecordsInterface $searchRecords,
    ) {
    }

    public function __invoke(Request $request)
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
        $searchRecordsParameters->setIncludeCodes($request->get('include_codes'));
        $searchRecordsParameters->setExcludeCodes($request->get('exclude_codes'));
        $searchRecordsParameters->setSearch($request->get('search'));
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
