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

use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use Akeneo\Tool\Component\Localization\Localizer\LocalizerInterface;

class DefaultValueProvider implements DefaultValuesProviderInterface
{
    /** @var DefaultValuesProviderInterface */
    protected $simpleProvider;

    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /** @var array */
    protected $supportedJobNames;

    /**
     * @param ChannelRepositoryInterface $channelRepository
     * @param LocaleRepositoryInterface $localeRepository
     * @param array $supportedJobNames
     */
    public function __construct(
        DefaultValuesProviderInterface $simpleProvider,
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository,
        array $supportedJobNames
    ) {
        $this->simpleProvider = $simpleProvider;
        $this->channelRepository = $channelRepository;
        $this->localeRepository = $localeRepository;
        $this->supportedJobNames = $supportedJobNames;
    }

    public function getDefaultValues()
    {
        $parameters = $this->simpleProvider->getDefaultValues();

        $parameters['decimalSeparator'] = LocalizerInterface::DEFAULT_DECIMAL_SEPARATOR;
        $parameters['dateFormat'] = LocalizerInterface::DEFAULT_DATE_FORMAT;
        $parameters['with_media'] = true;

        $channels = $this->channelRepository->getFullChannels();
        $defaultChannelCode = (0 !== count($channels)) ? $channels[0]->getCode() : null;

        $localesCodes = $this->localeRepository->getActivatedLocaleCodes();
        $defaultLocaleCodes = (0 !== count($localesCodes)) ? [$localesCodes[0]] : [];

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
        ];

        return $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobInterface $job)
    {
        return in_array($job->getName(), $this->supportedJobNames);
    }
}
