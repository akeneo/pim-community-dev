<?php

namespace spec\Pim\Bundle\ImportExportBundle\JobTemplate;

use Akeneo\Component\Batch\Model\JobInstance;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\ImportExportBundle\JobTemplate\JobTemplateProvider;
use Prophecy\Argument;

class JobTemplateProviderSpec extends ObjectBehavior
{
    function let()
    {
        $jobTemplateConfigurations = [
            'my_custom_job' => [
                'templates' => [
                    'edit' => 'edit_overridden_template',
                    'show' => 'show_overridden_template',
                ],
            ],
        ];
        $this->beConstructedWith($jobTemplateConfigurations);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\ImportExportBundle\JobTemplate\JobTemplateProvider');
    }

    function it_is_a_job_template_provider()
    {
        $this->shouldImplement('Pim\Bundle\ImportExportBundle\JobTemplate\JobTemplateProviderInterface');
    }

    function it_retrieves_overridden_job_templates(JobInstance $jobInstance)
    {
        $jobInstance->getAlias()->willReturn('my_custom_job');

        $this->getShowTemplate($jobInstance)->shouldReturn('show_overridden_template');
        $this->getEditTemplate($jobInstance)->shouldReturn('edit_overridden_template');
    }

    function it_generates_default_import_job_template(JobInstance $jobInstance)
    {
        $jobInstance->getAlias()->willReturn('an_unknown_job');
        $jobInstance->getType()->willReturn('import');

        $expectedJobTemplate = sprintf(JobTemplateProvider::DEFAULT_CREATE_TEMPLATE, 'Import');
        $this->getCreateTemplate($jobInstance)->shouldReturn($expectedJobTemplate);

        $expectedJobTemplate = sprintf(JobTemplateProvider::DEFAULT_SHOW_TEMPLATE, 'Import');
        $this->getShowTemplate($jobInstance)->shouldReturn($expectedJobTemplate);

        $expectedJobTemplate = sprintf(JobTemplateProvider::DEFAULT_EDIT_TEMPLATE, 'Import');
        $this->getEditTemplate($jobInstance)->shouldReturn($expectedJobTemplate);
    }

    function it_generates_default_export_job_template(JobInstance $jobInstance)
    {
        $jobInstance->getAlias()->willReturn('an_unknown_job');
        $jobInstance->getType()->willReturn('export');

        $expectedJobTemplate = sprintf(JobTemplateProvider::DEFAULT_CREATE_TEMPLATE, 'Export');
        $this->getCreateTemplate($jobInstance)->shouldReturn($expectedJobTemplate);

        $expectedJobTemplate = sprintf(JobTemplateProvider::DEFAULT_SHOW_TEMPLATE, 'Export');
        $this->getShowTemplate($jobInstance)->shouldReturn($expectedJobTemplate);

        $expectedJobTemplate = sprintf(JobTemplateProvider::DEFAULT_EDIT_TEMPLATE, 'Export');
        $this->getEditTemplate($jobInstance)->shouldReturn($expectedJobTemplate);
    }
}
