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

namespace Akeneo\Platform\Syndication\Infrastructure\Controller;

use Akeneo\Platform\Syndication\Domain\Query\Platform\FindPlatformInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class GetPlatformAction
{
    public function __construct(private FindPlatformInterface $findPlatform)
    {
    }

    public function __invoke(Request $request, string $platformCode): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $familyList = $this->findPlatform->byCode($platformCode);

        return (new JsonResponse($familyList));
    }
}
