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

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class DashboardKeyIndicatorsController
{
    public function __invoke(Request $request, string $channel, string $locale)
    {
        return new JsonResponse([
            'has_image' => [
                'ratio' => 18,
                'total' => 5641,
            ],
            'good_enrichment' => [
                'ratio' => 35.48,
                'total' => 15479,
            ],
            'values_perfect_spelling' => [
                'ratio' => 65,
                'total' => 6548,
            ],
            'attributes_perfect_spelling' => [
                'ratio' => 91,
                'total' => 1244,
            ],
        ]);
    }
}
