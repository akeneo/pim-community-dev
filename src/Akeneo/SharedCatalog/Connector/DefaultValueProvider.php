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

namespace Akeneo\SharedCatalog\Connector;

use Akeneo\Channel\Infrastructure\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Infrastructure\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\SharedCatalog\Model\SharedCatalog;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use Akeneo\Tool\Component\Localization\Localizer\LocalizerInterface;

class DefaultValueProvider implements DefaultValuesProviderInterface
{
    public function __construct(
        private DefaultValuesProviderInterface $simpleProvider,
        private ChannelRepositoryInterface $channelRepository,
        private LocaleRepositoryInterface $localeRepository,
        private array $supportedJobNames
    ) {
    }

    public function getDefaultValues(): array
    {
        $parameters = $this->simpleProvider->getDefaultValues();

        $parameters['decimalSeparator'] = LocalizerInterface::DEFAULT_DECIMAL_SEPARATOR;
        $parameters['dateFormat'] = LocalizerInterface::DEFAULT_DATE_FORMAT;
        $parameters['with_media'] = true;

        $channels = $this->channelRepository->getFullChannels();
        $defaultChannelCode = (empty($channels)) ? null : $channels[0]->getCode();

        $localesCodes = $this->localeRepository->getActivatedLocaleCodes();
        $defaultLocaleCodes = (empty($localesCodes)) ? [] : [$localesCodes[0]];

        $parameters['filters'] = [
            'data' => [
                [
                    'field' => 'enabled',
                    'operator' => Operators::EQUALS,
                    'value' => true,
                ],
                [
                    'field' => 'completeness',
                    'operator' => Operators::GREATER_OR_EQUAL_THAN,
                    'value' => 100,
                ],
                [
                    'field' => 'categories',
                    'operator' => Operators::IN_CHILDREN_LIST,
                    'value' => []
                ]
            ],
            'structure' => [
                'scope' => $defaultChannelCode,
                'locales' => $defaultLocaleCodes,
            ],
        ];

        $parameters['publisher'] = null;
        $parameters['recipients'] = [];
        $parameters['branding'] = [
            'image' => null,
            'cover_image' => null,
            'color' => SharedCatalog::DEFAULT_COLOR,
        ];

        return $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobInterface $job): bool
    {
        return in_array($job->getName(), $this->supportedJobNames);
    }
}
