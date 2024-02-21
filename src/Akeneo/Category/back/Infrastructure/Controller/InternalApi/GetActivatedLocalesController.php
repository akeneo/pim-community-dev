<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Controller\InternalApi;

use Akeneo\Channel\API\Query\FindLocales;
use Akeneo\Channel\API\Query\Locale;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetActivatedLocalesController
{
    public function __construct(
        private readonly FindLocales $findLocales,
    ) {
    }

    public function __invoke(): JsonResponse
    {
        $locales = $this->findLocales->findAllActivated();
        $localeCodes = array_map(fn (Locale $locale) => $locale->getCode(), $locales);

        return new JsonResponse($localeCodes, Response::HTTP_OK);
    }
}
