<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Controller\Dashboard;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Dashboard\GetCatalogProductScoreEvolutionQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CategoryCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class DashboardQualityScoreEvolutionController
{
    private GetCatalogProductScoreEvolutionQueryInterface $getCatalogProductScoreEvolution;

    public function __construct(GetCatalogProductScoreEvolutionQueryInterface $getCatalogProductScoreEvolution)
    {
        $this->getCatalogProductScoreEvolution = $getCatalogProductScoreEvolution;
    }

    public function __invoke(Request $request, string $channel, string $locale)
    {
        try {
            if ($request->query->has('category')) {
                $category = new CategoryCode($request->query->get('category'));
                $qualityScoreEvolution = $this->getCatalogProductScoreEvolution->byCategory(new ChannelCode($channel), new LocaleCode($locale), $category);
            } elseif ($request->query->has('family')) {
                $family = new FamilyCode($request->query->get('family'));
                $qualityScoreEvolution = $this->getCatalogProductScoreEvolution->byFamily(new ChannelCode($channel), new LocaleCode($locale), $family);
            } else {
                $qualityScoreEvolution = $this->getCatalogProductScoreEvolution->byCatalog(new ChannelCode($channel), new LocaleCode($locale));
            }
        } catch (\InvalidArgumentException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse($qualityScoreEvolution);
    }
}
