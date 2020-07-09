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
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\IgnoredTitleSuggestionRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\TitleSuggestion;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Events\TitleSuggestionIgnoredEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class IgnoreTitleSuggestionController
{
    private $featureFlag;

    private $ignoredTitleSuggestionRepository;

    private $eventDispatcher;

    public function __construct(
        FeatureFlag $featureFlag,
        IgnoredTitleSuggestionRepositoryInterface $ignoredTitleSuggestionRepository,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->featureFlag = $featureFlag;
        $this->eventDispatcher = $eventDispatcher;
        $this->ignoredTitleSuggestionRepository = $ignoredTitleSuggestionRepository;
    }

    public function __invoke(Request $request)
    {
        if (!$this->featureFlag->isEnabled()) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        try {
            $title = new TitleSuggestion($request->request->get('title'));
            $channelCode = new ChannelCode($request->request->get('channel'));
            $localeCode = new LocaleCode($request->request->get('locale'));
            $productId = new ProductId($request->request->getInt('productId'));

            $this->ignoredTitleSuggestionRepository->save(new Write\IgnoredTitleSuggestion(
                $productId,
                $channelCode,
                $localeCode,
                $title
            ));

            $this->eventDispatcher->dispatch(new TitleSuggestionIgnoredEvent($productId), TitleSuggestionIgnoredEvent::TITLE_SUGGESTION_IGNORED);

            return new Response(null, Response::HTTP_CREATED);
        } catch (\InvalidArgumentException $e) {
            return new Response(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Throwable $e) {
            return new Response(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
