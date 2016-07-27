<?php

namespace spec\Pim\Bundle\ImportExportBundle\JobParameters\FormConfigurationProvider;

use Akeneo\Component\Batch\Job\JobInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\ImportExportBundle\JobParameters\FormConfigurationProviderInterface;

class ProductXlsxExportSpec extends ObjectBehavior
{
    function let(
        FormConfigurationProviderInterface $simpleCsvExport
    ) {
        $this->beConstructedWith(
            $simpleCsvExport,
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
        $familyRepository
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
            'with_media' => [
                'type'    => 'switch',
                'options' => [
                    'label' => 'pim_connector.export.with_media.label',
                    'help'  => 'pim_connector.export.with_media.help'
                ]
            ],
        ];

        $exportConfig = [
            'filters' => [
                'type' => 'hidden',
                'options' => [
                    'attr' => [
                        'data-tab' => 'content'
                    ]
                ]
            ],
            'decimalSeparator' => [
                'type'    => 'choice',
                'options' => [
                    'choices'  => [',', ';'],
                    'required' => true,
                    'select2'  => true,
                    'label'    => 'pim_connector.export.decimalSeparator.label',
                    'help'     => 'pim_connector.export.decimalSeparator.help'
                ]
            ],
            'dateFormat' => [
                'type'    => 'choice',
                'options' => [
                    'choices'  => ['yyyy-MM-dd', 'dd/MM/yyyy'],
                    'required' => true,
                    'select2'  => true,
                    'label'    => 'pim_connector.export.dateFormat.label',
                    'help'     => 'pim_connector.export.dateFormat.help',
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

        $simpleCsvExport->getFormConfiguration()->willReturn($baseExport);

        $this->getFormConfiguration()->shouldReturn($exportConfig + $baseExport);
    }
}
