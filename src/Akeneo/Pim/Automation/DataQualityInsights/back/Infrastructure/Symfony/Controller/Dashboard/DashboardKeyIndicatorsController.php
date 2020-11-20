<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Controller\Dashboard;

use Akeneo\Pim\Automation\DataQualityInsights\Application\GetKeyIndicatorsInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CategoryCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DashboardKeyIndicatorsController
{
    private GetKeyIndicatorsInterface $getKeyIndicators;

    public function __construct(GetKeyIndicatorsInterface $getKeyIndicators)
    {
        $this->getKeyIndicators = $getKeyIndicators;
    }

    public function __invoke(Request $request, string $channel, string $locale)
    {
        try {
            $channel = new ChannelCode($channel);
            $locale = new LocaleCode($locale);

            if ($request->query->has('category')) {
                $category = new CategoryCode($request->query->get('category'));
                $keyIndicators = $this->getKeyIndicators->byCategory($channel, $locale, $category);
            } elseif ($request->query->has('family')) {
                $family = new FamilyCode($request->query->get('family'));
                $keyIndicators = $this->getKeyIndicators->byFamily($channel, $locale, $family);
            } else {
                $keyIndicators = $this->getKeyIndicators->all($channel, $locale);
            }
        } catch (\InvalidArgumentException $exception) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse($keyIndicators);
    }
}
