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

use Akeneo\Platform\TailoredImport\Application\GetGroupedTargets\GetGroupedTargetsHandler;
use Akeneo\Platform\TailoredImport\Application\GetGroupedTargets\GetGroupedTargetsQuery;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class GetGroupedTargetsAction
{
    public function __construct(
        private GetGroupedTargetsHandler $getGroupedTargetsHandler,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $options = $request->get('options', []);

        $getGroupedTargetsQuery = new GetGroupedTargetsQuery();
        $getGroupedTargetsQuery->search = $request->get('search');
        $getGroupedTargetsQuery->systemOffset = (int) $options['offset']['system'];
        $getGroupedTargetsQuery->attributeOffset = (int) $options['offset']['attribute'];
        $getGroupedTargetsQuery->limit = (int) ($options['limit'] ?? GetGroupedTargetsQuery::DEFAULT_LIMIT);
        $getGroupedTargetsQuery->locale = $options['locale'] ?? GetGroupedTargetsQuery::DEFAULT_LOCALE;

        $groupedTargets = $this->getGroupedTargetsHandler->handle($getGroupedTargetsQuery);

        return new JsonResponse($groupedTargets->normalize());
    }
}
