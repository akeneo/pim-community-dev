<?php

namespace spec\Pim\Bundle\ImportExportBundle\JobParameters\FormConfigurationProvider;

use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Model\JobInstance;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\ImportExportBundle\JobParameters\FormConfigurationProviderInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\FamilyRepositoryInterface;

class ProductXlsxExportSpec extends ObjectBehavior
{
    function let(
        FormConfigurationProviderInterface $simpleCsvExport,
        ChannelRepositoryInterface $channelRepository,
        FamilyRepositoryInterface $familyRepository
    ) {
        $this->beConstructedWith(
            $simpleCsvExport,
            $channelRepository,
            $familyRepository,
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
        $familyRepository,
        JobInstance $jobInstance
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

        $channelCodes = [
            'mobile'    => 'Mobile',
            'ecommerce' => 'E-commerce'
        ];

        $channelRepository->getLabelsIndexedByCode()->willReturn($channelCodes);

        $exportConfig = [
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
            'locales' => ['type' => 'pim_import_export_product_export_locale_choice'],
            'families' => [
                'type'    => 'select_family_type',
                'options' => [
                    'repository' => $familyRepository,
                    'route' => 'pim_enrich_family_rest_index',
                    'required' => false,
                    'multiple' => true,
                    'label' => 'pim_base_connector.export.families.label',
                    'help' => 'pim_base_connector.export.families.help',
                    'attr' => [
                        'data-tab' => 'content',
                        'data-placeholder' => 'pim_base_connector.export.families.placeholder'
                    ]
                ]
            ],
            'product_identifier' => [
                'type'    => 'pim_product_identifier_choice',
                'options' => [
                    'multiple'    => true,
                    'label'       => 'pim_connector.export.product_identifier.label',
                    'help'        => 'pim_connector.export.product_identifier.help',
                    'placeholder' => 'pim_connector.export.product_identifier.placeholder',
                    'attr'        => [
                        'data-tab' => 'content',
                    ]
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
            'completeness' => [
                'type'    => 'choice',
                'options' => [
                    'choices'  => [
                        'at_least_one_complete' => 'pim_connector.export.completeness.choice.at_least_one_complete',
                        'all_complete'          => 'pim_connector.export.completeness.choice.all_complete',
                        'all_incomplete'        => 'pim_connector.export.completeness.choice.all_incomplete',
                        'all'                   => 'pim_connector.export.completeness.choice.all'
                    ],
                    'required' => true,
                    'select2'  => true,
                    'label'    => 'pim_connector.export.completeness.label',
                    'help'     => 'pim_connector.export.completeness.help',
                    'attr'     => ['data-tab' => 'content']
                ]
            ],
            'updated_since' => [
                'type'    => 'pim_updated_since_parameter_type',
                'options' => [
                    'job_instance' => $jobInstance,
                    'label'        => 'pim_connector.export.updated.updated_since_strategy.label',
                    'help'         => 'pim_connector.export.updated.updated_since_strategy.help',
                    'attr'         => ['data-tab' => 'content']
                ]
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

        $simpleCsvExport->getFormConfiguration($jobInstance)->willReturn($baseExport);

        $this->getFormConfiguration($jobInstance)->shouldReturn($exportConfig + $baseExport);
    }
}
