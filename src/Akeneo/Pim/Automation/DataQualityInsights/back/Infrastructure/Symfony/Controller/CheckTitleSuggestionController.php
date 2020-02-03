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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Controller;

use Akeneo\Pim\Automation\DataQualityInsights\Application\FeatureFlag;
use Akeneo\Pim\Automation\DataQualityInsights\Application\GetProductTitleSuggestion;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class CheckTitleSuggestionController
{
    private $featureFlag;
    /**
     * @var GetProductTitleSuggestion
     */
    private $getProductTitleSuggestion;

    public function __construct(FeatureFlag $featureFlag, GetProductTitleSuggestion $getProductTitleSuggestion)
    {
        $this->featureFlag = $featureFlag;
        $this->getProductTitleSuggestion = $getProductTitleSuggestion;
    }

    public function __invoke(Request $request)
    {
        if (!$this->featureFlag->isEnabled()) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        $productId = new ProductId($request->request->getInt('productId'));
        $channelCode = new ChannelCode($request->request->get('channel'));
        $localeCode = new LocaleCode($request->request->get('locale'));

        $titleSuggestion = $this->getProductTitleSuggestion->get($productId, $channelCode, $localeCode);

        return new JsonResponse($titleSuggestion, Response::HTTP_OK);
    }
}
