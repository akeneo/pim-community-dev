<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation;

use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\Channels\InMemoryChannels;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\Locales\InMemoryLocales;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TransformChannelLocaleDataIdsSpec extends ObjectBehavior
{
    public function let()
    {
        $channels = new InMemoryChannels([
            'ecommerce' => 1,
            'mobile' => 2,
        ]);

        $locales = new InMemoryLocales([
            'en_US' => 58,
            'fr_FR' => 90,
        ]);

        $this->beConstructedWith($channels, $locales);
    }

    public function it_transforms_channels_and_locales_with_data_from_ids_to_codes(): void
    {
        $dataToTransform = [
            1 => [
                58 => [12, 34],
                90 => [34],
            ],
            2 => [
                58 => [12, 34, 56],
            ],
        ];

        $expectedTransformedData = [
            'ecommerce' => [
                'en_US' => 2,
                'fr_FR' => 1,
            ],
            'mobile' => [
                'en_US' => 3,
            ],
        ];

        $this->transformToCodes($dataToTransform, fn ($elements) => count($elements))->shouldReturn($expectedTransformedData);
    }

    public function it_removes_unknown_channels_and_locales_during_transformation(): void
    {
        $dataToTransform = [
            1 => [
                58 => [12, 34],
                789 => [34],
                90 => [34],
            ],
            76 => [
                58 => [12, 34, 56],
            ],
        ];

        $expectedTransformedData = [
            'ecommerce' => [
                'en_US' => 2,
                'fr_FR' => 1,
            ],
        ];

        $this->transformToCodes($dataToTransform, fn ($elements) => count($elements))->shouldReturn($expectedTransformedData);
    }
}
