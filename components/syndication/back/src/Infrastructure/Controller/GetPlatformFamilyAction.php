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

use Akeneo\Platform\Syndication\Domain\Query\Platform\FindPlatformFamilyInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class GetPlatformFamilyAction
{
    public function __construct(private FindPlatformFamilyInterface $findPlatformFamily)
    {
    }

    public function __invoke(Request $request, string $platformCode, $familyCode): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $family = $this->findPlatformFamily->byPlatformCodeAndFamilyCode($platformCode, $familyCode);

        return (new JsonResponse($family));
    }
}
