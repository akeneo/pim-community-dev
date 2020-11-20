<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Controller\Dashboard;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Dashboard\GetAverageRanksQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class DashboardWidgetFamiliesController
{
    /** @var GetAverageRanksQueryInterface */
    private $getAverageRanks;

    public function __construct(GetAverageRanksQueryInterface $getAverageRanks)
    {
        $this->getAverageRanks = $getAverageRanks;
    }

    public function __invoke(Request $request, string $channel, string $locale)
    {
        try {
            $channelCode = new ChannelCode($channel);
            $localeCode = new LocaleCode($locale);
            $familyCodes = $this->getFamilyCodesFromRequest($request);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }

        $averageRanks = $this->getAverageRanks->byFamilies($channelCode, $localeCode, $familyCodes);

        return new JsonResponse($averageRanks);
    }

    private function getFamilyCodesFromRequest(Request $request): array
    {
        $requestFamilies = $request->get('families', []);

        if (!is_array($requestFamilies)) {
            throw new \InvalidArgumentException('The list of families must be an array');
        }

        return array_map(function ($familyCode) {
            return new FamilyCode($familyCode);
        }, $requestFamilies);
    }
}
