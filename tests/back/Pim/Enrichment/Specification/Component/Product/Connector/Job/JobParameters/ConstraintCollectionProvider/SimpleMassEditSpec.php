<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\Job\JobParameters\ConstraintCollectionProvider;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Job\JobParameters\ConstraintCollectionProvider\SimpleMassEdit;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Required;
use Symfony\Component\Validator\Constraints\Type;

class SimpleMassEditSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(['product_mass_edit']);
    }

    public function it_is_a_constraint_collection_provider(): void
    {
        $this->shouldHaveType(SimpleMassEdit::class);
        $this->shouldImplement(ConstraintCollectionProviderInterface::class);
    }

    public function it_provides_a_collection_of_constraints(): void
    {
        /** @var Collection $constraints */
        $constraints = $this->getConstraintCollection();
        $constraints->shouldBeAnInstanceOf(Collection::class);
        $fields = $constraints->fields;

        $fields['filters']->shouldBeAnInstanceOf(Required::class);
        $fields['filters']->constraints[0]->shouldBeAnInstanceOf(NotNull::class);

        $fields['actions']->shouldBeAnInstanceOf(Required::class);
        $fields['actions']->constraints[0]->shouldBeAnInstanceOf(NotNull::class);

        $fields['user_to_notify']->shouldBeAnInstanceOf(Required::class);
        $fields['user_to_notify']->constraints[0]->shouldBeAnInstanceOf(Type::class);
        $fields['user_to_notify']->constraints[0]->type->shouldBe('string');

        $fields['is_user_authenticated']->shouldBeAnInstanceOf(Required::class);
        $fields['is_user_authenticated']->constraints[0]->shouldBeAnInstanceOf(Type::class);
        $fields['is_user_authenticated']->constraints[0]->type->shouldBe('bool');

        $fields['origin']->shouldBeAnInstanceOf(Required::class);
        $fields['origin']->constraints[0]->shouldBeAnInstanceOf(Type::class);
        $fields['origin']->constraints[0]->type->shouldBe('string');
    }

    public function it_supports_jobs(JobInterface $jobMassEdit, JobInterface $jobImport): void
    {
        $jobMassEdit->getName()->willReturn('product_mass_edit');
        $jobImport->getName()->willReturn('product_import');

        $this->supports($jobMassEdit)->shouldReturn(true);
        $this->supports($jobImport)->shouldReturn(false);
    }
}
