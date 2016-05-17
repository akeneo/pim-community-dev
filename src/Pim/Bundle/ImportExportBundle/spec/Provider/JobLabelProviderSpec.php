<?php

namespace spec\Pim\Bundle\ImportExportBundle\Provider;

use PhpSpec\ObjectBehavior;
use Symfony\Component\Translation\TranslatorInterface;

class JobLabelProviderSpec extends ObjectBehavior
{
    function let(TranslatorInterface $translator)
    {
        $this->beConstructedWith($translator, 'batch_jobs');
    }

    function it_returns_a_job_label($translator)
    {
        $translator->trans('batch_jobs.csv_product_import.label')->willReturn('CSV Product Import');
        $this->getJobLabel('csv_product_import')
            ->shouldReturn('CSV Product Import');
    }

    function it_returns_a_step_label($translator)
    {
        $translator->trans('batch_jobs.csv_product_import.perform.label')->willReturn('Import Products');
        $this->getStepLabel('csv_product_import', 'perform')
            ->shouldReturn('Import Products');
    }

    function it_returns_a_step_warning_label($translator)
    {
        $translator->trans('batch_jobs.csv_product_import.perform.warning.duplicated.label')->willReturn('Duplicated products');
        $this->getStepWarningLabel('csv_product_import', 'perform', 'duplicated')
            ->shouldReturn('Duplicated products');
    }
}
