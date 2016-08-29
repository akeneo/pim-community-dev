<?php

namespace Pim\Component\Connector\Job\JobParameters\DefaultValuesProvider;

use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use Akeneo\Component\Localization\Localizer\LocalizerInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;

/**
 * DefaultParameters for product CSV export
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCsvExport implements DefaultValuesProviderInterface
{
    /** @var DefaultValuesProviderInterface */
    protected $simpleProvider;

    /** @var array */
    protected $supportedJobNames;

    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /**
     * @param DefaultValuesProviderInterface $simpleProvider
     * @param ChannelRepositoryInterface     $channelRepository
     * @param LocaleRepositoryInterface      $localeRepository
     * @param array                          $supportedJobNames
     */
    public function __construct(
        DefaultValuesProviderInterface $simpleProvider,
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository,
        array $supportedJobNames
    ) {
        $this->simpleProvider    = $simpleProvider;
        $this->channelRepository = $channelRepository;
        $this->localeRepository  = $localeRepository;
        $this->supportedJobNames = $supportedJobNames;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultValues()
    {
        $parameters = $this->simpleProvider->getDefaultValues();
        $parameters['decimalSeparator'] = LocalizerInterface::DEFAULT_DECIMAL_SEPARATOR;
        $parameters['dateFormat'] = LocalizerInterface::DEFAULT_DATE_FORMAT;
        $parameters['with_media'] = true;

        $basePath = sys_get_temp_dir();
        if (DIRECTORY_SEPARATOR !== substr($basePath, -1)) {
            $basePath = $basePath . DIRECTORY_SEPARATOR;
        }
        $parameters['filePath'] = $basePath . 'csv_products_export.csv';

        $defaultChannel = $this->channelRepository->getFullChannels()[0];
        $defaultLocaleCode = $this->localeRepository->getActivatedLocaleCodes()[0];
        $parameters['filters'] = [
            'data'      => [
                [
                    'field'    => 'enabled',
                    'operator' => OPERATORS::EQUALS,
                    'value'    => true,
                ],
                [
                    'field'    => 'completeness',
                    'operator' => OPERATORS::GREATER_OR_EQUAL_THAN,
                    'value'    => 100,
                ],
                [
                    'field'    => 'categories.code',
                    'operator' => OPERATORS::IN_CHILDREN_LIST,
                    'value'    => []
                ]
            ],
            'structure' => [
                'scope'   => $defaultChannel->getCode(),
                'locales' => [$defaultLocaleCode],
            ],
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
