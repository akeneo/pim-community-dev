<?php

namespace spec\Pim\Bundle\ImportExportBundle\JobParameters\FormConfigurationProvider;

use Akeneo\Component\Batch\Job\BatchStatus;
use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\Localization\Presenter\PresenterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\Resolver\LocaleResolver;
use Pim\Bundle\ImportExportBundle\JobParameters\FormConfigurationProviderInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ProductXlsxExportSpec extends ObjectBehavior
{
    function let(
        FormConfigurationProviderInterface $simpleCsvExport,
        ChannelRepositoryInterface $channelRepository,
        JobRepositoryInterface $jobRepository,
        TranslatorInterface $translator,
        PresenterInterface $datePresenter,
        LocaleResolver $localeResolver
    ) {

        $this->beConstructedWith(
            $simpleCsvExport,
            $channelRepository,
            $jobRepository,
            $translator,
            $datePresenter,
            $localeResolver,
            ['product_xlsx_export'],
            [',', ';'],
            ['yyyy-MM-dd', 'dd/MM/yyyy']
        );
    }

    function it_is_a_form_configuration()
    {
        $this->shouldImplement('Pim\Bundle\ImportExportBundle\JobParameters\FormConfigurationProviderInterface');
    }

    function it_supports(JobInterface $job)
    {
        $job->getName()->willReturn('product_xlsx_export');
        $this->supports($job)->shouldReturn(true);

        $job->getName()->willReturn('product_csv_export');
        $this->supports($job)->shouldReturn(false);
    }

    function it_gets_form_configuration(
        $simpleCsvExport,
        $channelRepository,
        $jobRepository,
        $translator,
        $datePresenter,
        $localeResolver,
        JobInstance $jobInstance,
        JobExecution $jobExecution
    ) {
        $baseExport = [
            'linesPerFile' => [
                'type'    => 'integer',
                'options' => [
                    'label' => 'pim_connector.export.lines_per_files.label',
                    'help'  => 'pim_connector.export.lines_per_files.help',
                ]
            ],
            'filePath' => [
                'options' => [
                    'label' => 'pim_connector.export.filePath.label',
                    'help'  => 'pim_connector.export.filePath.help'
                ]
            ],
            'withHeader' => [
                'type'    => 'switch',
                'options' => [
                    'label' => 'pim_connector.export.withHeader.label',
                    'help'  => 'pim_connector.export.withHeader.help'
                ]
            ],
        ];

        $simpleCsvExport->getFormConfiguration($jobInstance)->willReturn($baseExport);

        $date = new \DateTime('2015-12-15 16:00:50');
        $jobExecution->getStartTime()->willReturn($date);
        $jobRepository->getLastJobExecution($jobInstance, BatchStatus::COMPLETED)->willReturn($jobExecution);

        $localeResolver->getCurrentLocale()->willReturn('fr_FR');
        $datePresenter->present($date, ['locale' => 'fr_FR'])->willReturn('15/12/2015 16:00:50');
        $translator->trans('pim_connector.export.updated.last_execution.last', [
            '%date%' => '15/12/2015 16:00:50'
        ])->willReturn('Last export: 15/12/2015 16:00:50');

        $channelCodes = [
            'mobile'    => 'Mobile',
            'ecommerce' => 'E-commerce'
        ];
        $channelRepository->getLabelsIndexedByCode()->willReturn($channelCodes);

        $result = $this->getConfiguration($channelCodes, 'Last export: 15/12/2015 16:00:50') + $baseExport;
        $this->getFormConfiguration($jobInstance)->shouldReturn($result);
    }

    function it_gets_form_configuration_when_job_has_never_been_exported(
        $simpleCsvExport,
        $channelRepository,
        $jobRepository,
        $translator,
        $datePresenter,
        $localeResolver,
        JobInstance $jobInstance,
        JobExecution $jobExecution
    ) {
        $baseExport = [];

        $simpleCsvExport->getFormConfiguration($jobInstance)->willReturn($baseExport);

        $date = new \DateTime('2015-12-15 16:00:50');
        $jobExecution->getStartTime()->willReturn($date);
        $jobRepository->getLastJobExecution($jobInstance, BatchStatus::COMPLETED)->willReturn(null);

        $localeResolver->getCurrentLocale()->shouldNotBeCalled();
        $datePresenter->present()->shouldNotBeCalled();
        $translator->trans('pim_connector.export.updated.last_execution.none')
            ->willReturn('This job has never been exported');

        $channelCodes = [
            'mobile'    => 'Mobile',
            'ecommerce' => 'E-commerce'
        ];
        $channelRepository->getLabelsIndexedByCode()->willReturn($channelCodes);

        $result = $this->getConfiguration($channelCodes, 'This job has never been exported') + $baseExport;
        $this->getFormConfiguration($jobInstance)->shouldReturn($result);
    }

    private function getConfiguration($channelCodes, $updatedInfo)
    {
        return [
            'channel' => [
                'type'    => 'choice',
                'options' => [
                    'choices'  => $channelCodes,
                    'required' => true,
                    'select2'  => true,
                    'label'    => 'pim_connector.export.channel.label',
                    'help'     => 'pim_connector.export.channel.help',
                    'attr'     => ['data-tab' => 'content']
                ]
            ],
            'enabled' => [
                'type'    => 'choice',
                'options' => [
                    'choices'  => [
                        'enabled'  => 'pim_connector.export.status.choice.enabled',
                        'disabled' => 'pim_connector.export.status.choice.disabled',
                        'all'      => 'pim_connector.export.status.choice.all'
                    ],
                    'required' => true,
                    'select2'  => true,
                    'label'    => 'pim_connector.export.status.label',
                    'help'     => 'pim_connector.export.status.help',
                    'attr'     => ['data-tab' => 'content']
                ]
            ],
            'updated' => [
                'type'    => 'choice',
                'options' => [
                    'choices'  => [
                        'all'         => 'pim_connector.export.updated.choice.all',
                        'last_export' => 'pim_connector.export.updated.choice.last_export'
                    ],
                    'required' => true,
                    'select2'  => true,
                    'label'    => 'pim_connector.export.updated.label',
                    'help'     => 'pim_connector.export.updated.help',
                    'info'     => $updatedInfo,
                    'attr'     => ['data-tab' => 'content']
                ],
            ],
            'decimalSeparator' => [
                'type'    => 'choice',
                'options' => [
                    'choices'  => [',', ';'],
                    'required' => true,
                    'select2'  => true,
                    'label'    => 'pim_base_connector.export.decimalSeparator.label',
                    'help'     => 'pim_base_connector.export.decimalSeparator.help'
                ]
            ],
            'dateFormat' => [
                'type'    => 'choice',
                'options' => [
                    'choices'  => ['yyyy-MM-dd', 'dd/MM/yyyy'],
                    'required' => true,
                    'select2'  => true,
                    'label'    => 'pim_base_connector.export.dateFormat.label',
                    'help'     => 'pim_base_connector.export.dateFormat.help',
                ]
            ],
            'linesPerFile' => [
                'type'    => 'integer',
                'options' => [
                    'label' => 'pim_connector.export.lines_per_files.label',
                    'help'  => 'pim_connector.export.lines_per_files.help',
                ]
            ],
        ];
    }
}
