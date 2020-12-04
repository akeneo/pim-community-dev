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
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CategoryCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class DashboardWidgetCategoriesController
{
    private GetAverageRanksQueryInterface $getAverageRanks;

    public function __construct(GetAverageRanksQueryInterface $getAverageRanks)
    {
        $this->getAverageRanks = $getAverageRanks;
    }

    public function __invoke(Request $request, string $channel, string $locale)
    {
        try {
            $channelCode = new ChannelCode($channel);
            $localeCode = new LocaleCode($locale);
            $categoryCodes = $this->getCategoryCodesFromRequest($request);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }

        $averageRanks = $this->getAverageRanks->byCategories($channelCode, $localeCode, $categoryCodes);

        return new JsonResponse($averageRanks);
    }

    private function getCategoryCodesFromRequest(Request $request): array
    {
        $requestCategories = $request->get('categories', []);

        if (!is_array($requestCategories)) {
            throw new \InvalidArgumentException('The list of categories must be an array');
        }

        return array_map(function ($categoryCode) {
            return new CategoryCode($categoryCode);
        }, $requestCategories);
    }
}
