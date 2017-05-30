<?php

namespace spec\Pim\Component\Connector\Job\JobParameters\DefaultValuesProvider;

use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;

class ProductXlsxExportSpec extends ObjectBehavior
{
    function let(
        DefaultValuesProviderInterface $decoratedProvider,
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository
    ) {
        $this->beConstructedWith($decoratedProvider, $channelRepository, $localeRepository, ['my_supported_job_name']);
    }

    function it_is_a_provider()
    {
        $this->shouldImplement('Akeneo\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface');
    }

    function it_provides_default_values(
        $decoratedProvider,
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository,
        LocaleInterface $locale,
        ChannelInterface $channel
    ) {
        $channel->getCode()->willReturn('channel_code');
        $channelRepository->getFullChannels()->willReturn([$channel]);

        $locale->getCode()->willReturn('locale_code');
        $localeRepository->getActivatedLocaleCodes()->willReturn([$locale]);

        $decoratedProvider->getDefaultValues()->willReturn(['decoratedParam' => true]);
        $this->getDefaultValues()->shouldReturnWellFormedDefaultValues();
    }

    function it_supports_a_job(JobInterface $job)
    {
        $job->getName()->willReturn('my_supported_job_name');
        $this->supports($job)->shouldReturn(true);
    }

    public function getMatchers()
    {
        return [
            'returnWellFormedDefaultValues' => function ($parameters) {
                return true === $parameters['decoratedParam'] &&
                    '.' === $parameters['decimalSeparator'] &&
                    'yyyy-MM-dd' === $parameters['dateFormat'] &&
                    true === $parameters['with_media'] &&
                    10000 === $parameters['linesPerFile'] &&
                    is_array($parameters['filters']) &&
                    is_array($parameters['filters']['data']) &&
                    is_array($parameters['filters']['structure']);
            }
        ];
    }
}
