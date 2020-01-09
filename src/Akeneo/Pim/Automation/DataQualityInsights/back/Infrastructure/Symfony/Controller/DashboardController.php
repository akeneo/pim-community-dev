<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Controller;

use Akeneo\Pim\Automation\DataQualityInsights\Application\FeatureFlag;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\DashboardRatesProjectionRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CategoryCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Periodicity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class DashboardController
{
    /** @var FeatureFlag */
    private $featureFlag;

    /** @var DashboardRatesProjectionRepositoryInterface */
    private $dashboardRatesProjectionRepository;

    public function __construct(FeatureFlag $featureFlag, DashboardRatesProjectionRepositoryInterface $dashboardRatesProjectionRepository)
    {
        $this->featureFlag = $featureFlag;
        $this->dashboardRatesProjectionRepository = $dashboardRatesProjectionRepository;
    }

    public function __invoke(Request $request, string $channel, string $locale, string $periodicity)
    {
        if (! $this->featureFlag->isEnabled()) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        if ($request->query->has('category')) {
            $category = new CategoryCode($request->query->getAlnum('category'));
            $rates = $this->dashboardRatesProjectionRepository->findCategoryProjection(new ChannelCode($channel), new LocaleCode($locale), new Periodicity($periodicity), $category);
        } elseif ($request->query->has('family')) {
            $family = new FamilyCode($request->query->getAlnum('family'));
            $rates = $this->dashboardRatesProjectionRepository->findFamilyProjection(new ChannelCode($channel), new LocaleCode($locale), new Periodicity($periodicity), $family);
        } else {
            $rates = $this->dashboardRatesProjectionRepository->findCatalogProjection(new ChannelCode($channel), new LocaleCode($locale), new Periodicity($periodicity));
        }

        if(empty($rates))
        {
            return new JsonResponse([]);
        }

        return new JsonResponse($rates->toArray());
    }
}
