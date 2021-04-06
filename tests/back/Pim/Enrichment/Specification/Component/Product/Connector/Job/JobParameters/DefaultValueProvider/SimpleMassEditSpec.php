<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\Job\JobParameters\DefaultValueProvider;

use Akeneo\Pim\Enrichment\Component\ContextOrigin;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Job\JobParameters\DefaultValueProvider\SimpleMassEdit;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use PhpSpec\ObjectBehavior;

class SimpleMassEditSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(['simple_mass_edit']);
    }

    public function it_is_a_default_value_provider(): void
    {
        $this->shouldHaveType(SimpleMassEdit::class);
        $this->shouldImplement(DefaultValuesProviderInterface::class);
    }

    public function it_provides_default_values(): void
    {
        $this->getDefaultValues()->shouldReturn([
            'filters'               => [],
            'actions'               => [],
            'user_to_notify'        => null,
            'is_user_authenticated' => false,
            'origin'                => ContextOrigin::MASS_EDIT,
        ]);
    }

    public function it_supports_jobs(JobInterface $jobMassEdit, JobInterface $jobImport): void
    {
        $jobMassEdit->getName()->willReturn('simple_mass_edit');
        $jobImport->getName()->willReturn('product_import');

        $this->supports($jobMassEdit)->shouldReturn(true);
        $this->supports($jobImport)->shouldReturn(false);
    }
}
