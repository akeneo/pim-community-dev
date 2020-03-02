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
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Psr\Log\LoggerInterface;
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

    /** @var LoggerInterface */
    private $logger;

    public function __construct(FeatureFlag $featureFlag, TextChecker $textChecker, SupportedLocaleChecker $supportedLocaleChecker, LoggerInterface $logger)
    {
        $this->featureFlag = $featureFlag;
        $this->textChecker = $textChecker;
        $this->supportedLocaleChecker = $supportedLocaleChecker;
        $this->logger = $logger;
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

        $this->logger->info('spelling evaluation', [
            'source' => 'pef',
            'value' => $text,
            'localeCode' => strval($localeCode)
        ]);

        $analysis = $this->textChecker->check($text, $localeCode);

        return new JsonResponse($analysis->normalize());
    }
}
