<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\TailoredImport\Infrastructure\Controller;

use Akeneo\Platform\TailoredImport\Infrastructure\Validation\GetRecords;
use Akeneo\ReferenceEntity\Infrastructure\PublicApi\Platform\SearchRecordsInterface;
use Akeneo\ReferenceEntity\Infrastructure\PublicApi\Platform\SearchRecordsParameters;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class GetRecordsAction
{
    private const LIMIT_DEFAULT = 25;

    public function __construct(
        private SearchRecordsInterface $searchRecords,
        private ValidatorInterface $validator,
        private NormalizerInterface $violationNormalizer,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $violations = $this->validator->validate($request, new GetRecords());
        if (0 < $violations->count()) {
            return new JsonResponse($this->violationNormalizer->normalize($violations), Response::HTTP_BAD_REQUEST);
        }

        $searchRecordsParameters = new SearchRecordsParameters();
        $searchRecordsParameters->setIncludeCodes($request->get('include_codes'));
        $searchRecordsParameters->setExcludeCodes($request->get('exclude_codes'));
        $searchRecordsParameters->setSearch($request->get('search'));
        $searchRecordsParameters->setLimit($request->get('limit', self::LIMIT_DEFAULT));
        $searchRecordsParameters->setPage($request->get('page', 1));

        $searchRecordsResult = $this->searchRecords->search(
            $request->get('reference_entity_code'),
            $request->get('channel'),
            $request->get('locale'),
            $searchRecordsParameters,
        );

        return new JsonResponse($searchRecordsResult->normalize());
    }
}
