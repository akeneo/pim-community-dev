<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\Job\JobParameters\DefaultValueProvider;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use PhpSpec\ObjectBehavior;

class ProductModelCsvExportSpec extends ObjectBehavior
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
        $this->shouldImplement(DefaultValuesProviderInterface::class);
    }

    function it_provides_default_values(
        $decoratedProvider,
        $channelRepository,
        $localeRepository,
        ChannelInterface $channel,
        LocaleInterface $locale
    ) {
        $decoratedProvider->getDefaultValues()->willReturn(['decoratedParam' => true]);

        $channelRepository->getFullChannels()->willReturn([$channel]);
        $channel->getCode()->willReturn('channel');

        $localeRepository->getActivatedLocaleCodes()->willReturn([$locale]);
        $locale->getCode()->willReturn('locale');

        $this->getDefaultValues()->shouldReturnWellFormedDefaultValues();
    }

    function it_supports_a_job(JobInterface $job)
    {
        $job->getName()->willReturn('my_supported_job_name');
        $this->supports($job)->shouldReturn(true);
    }

    public function getMatchers(): array
    {
        return [
            'returnWellFormedDefaultValues' => function ($parameters) {
                return true === $parameters['decoratedParam'] &&
                    true === $parameters['with_media'];
            }
        ];
    }
}
