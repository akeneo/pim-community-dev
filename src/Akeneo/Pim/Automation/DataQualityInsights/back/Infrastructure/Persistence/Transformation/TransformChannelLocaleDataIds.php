<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation;

use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\Channels\ChannelsInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\Locales\LocalesInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TransformChannelLocaleDataIds
{
    public function __construct(
        private ChannelsInterface $channels,
        private LocalesInterface $locales
    ) {
    }

    public function transformToCodes(array $channelLocaleData, \Closure $transformData): array
    {
        $channelLocaleDataByCodes = [];

        foreach ($channelLocaleData as $channelId => $localeData) {
            $channelCode = $this->channels->getCodeById($channelId);
            if (null === $channelCode) {
                continue;
            }

            foreach ($localeData as $localeId => $data) {
                $localeCode = $this->locales->getCodeById($localeId);
                if (null === $localeCode) {
                    continue;
                }

                $channelLocaleDataByCodes[$channelCode][$localeCode] = $transformData($data);
            }
        }

        return $channelLocaleDataByCodes;
    }
}
