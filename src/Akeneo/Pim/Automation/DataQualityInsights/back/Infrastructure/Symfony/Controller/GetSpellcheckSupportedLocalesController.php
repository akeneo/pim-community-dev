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

use Akeneo\Pim\Automation\DataQualityInsights\Application\Spellcheck\SupportedLocaleValidator;
use Symfony\Component\HttpFoundation\JsonResponse;

final class GetSpellcheckSupportedLocalesController
{
    private SupportedLocaleValidator $supportedLocaleValidator;

    public function __construct(SupportedLocaleValidator $supportedLocaleValidator)
    {
        $this->supportedLocaleValidator = $supportedLocaleValidator;
    }

    public function __invoke()
    {
        $localeCodes = [];
        foreach ($this->supportedLocaleValidator->getSupportedLocaleCollection() as $languageCode => $localeCollection) {
            $localeCodes = array_merge($localeCodes, $localeCollection->toArrayString());
        }

        return new JsonResponse($localeCodes);
    }
}
