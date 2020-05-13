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

use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency\SupportedLocaleChecker;
use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency\TextChecker;
use Akeneo\Pim\Automation\DataQualityInsights\Application\FeatureFlag;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Exception\TextCheckFailedException;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class CheckTextController
{
    private $featureFlag;

    private $textChecker;

    private $supportedLocaleChecker;

    public function __construct(FeatureFlag $featureFlag, TextChecker $textChecker, SupportedLocaleChecker $supportedLocaleChecker)
    {
        $this->featureFlag = $featureFlag;
        $this->textChecker = $textChecker;
        $this->supportedLocaleChecker = $supportedLocaleChecker;
    }

    public function __invoke(Request $request)
    {
        if (!$this->featureFlag->isEnabled()) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        $text = $request->request->get('text');
        $localeCode = new LocaleCode($request->request->get('locale'));

        // @todo[DAPI-601] can we use a more appropriate response ?
        if (empty($text) || !$this->supportedLocaleChecker->isSupported($localeCode)) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        try {
            $analysis = $this->textChecker->check($text, $localeCode);
        } catch (TextCheckFailedException $e) {
            return new Response(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse($analysis->normalize());
    }
}
