<?php

namespace spec\Pim\Bundle\ImportExportBundle\Form\Type\JobParameter;

use Akeneo\Component\Batch\Job\BatchStatus;
use Akeneo\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\Localization\Factory\DateFactory;
use Akeneo\Component\Localization\Presenter\PresenterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\Resolver\LocaleResolver;
use Pim\Bundle\ImportExportBundle\Constraints\UpdatedSinceStrategy;
use Prophecy\Argument;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\Callback;

class UpdatedSinceTypeSpec extends ObjectBehavior
{
    function let(
        JobRepositoryInterface $jobRepository,
        TranslatorInterface $translator,
        LocaleResolver $localeResolver,
        PresenterInterface $datePresenter,
        DateFactory $dateFactory
    ) {
        $this->beConstructedWith(
            $jobRepository, 
            $translator, 
            $localeResolver, 
            $datePresenter, 
            $dateFactory,
            'Akeneo\Component\Batch\Model\JobInstance'
        );
    }
    
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\ImportExportBundle\Form\Type\JobParameter\UpdatedSinceType');
    }

    function it_is_a_symfony_form()
    {
        $this->shouldHaveType('Symfony\Component\Form\AbstractType');
    }

    function it_builds_a_form(
        $dateFactory,
        $localeResolver,
        FormBuilderInterface $builder,
        JobInstance $jobInstance,
        \IntlDateFormatter $dateFormatter
    ) {
        $localeResolver->getCurrentLocale()->willReturn('en_US');
        $dateFactory->create(['locale' => 'en_US'])->willReturn($dateFormatter);
        $dateFormatter->getPattern()->willReturn('y-m-d');
        
        $builder->add('updated_since_strategy', 'choice', [
            'choices'  => [
                'all'         => 'pim_connector.export.updated.updated_since_strategy.choice.all',
                'last_export' => 'pim_connector.export.updated.updated_since_strategy.choice.last_export',
                'since_date'  => 'pim_connector.export.updated.updated_since_strategy.choice.since_date',
            ],
            'select2'  => true,
            'label'    => false,
        ])->willReturn($builder);

        $builder->add('updated_since_date', 'datetime', Argument::that(function ($value) {
            return 
                isset($value['widget']) && 'single_text' === $value['widget'] &&
                isset($value['format']) && 'y-m-d' === $value['format'] &&
                isset($value['input']) && 'string' === $value['input'] &&
                isset($value['constraints']) && $value['constraints'] instanceof UpdatedSinceStrategy
            ;
        }))->shouldBeCalled();

        $this->buildForm($builder, ['job_instance' => $jobInstance]);
    }

    function it_builds_the_form_view_without_info(
        JobInstance $jobInstance,
        FormInterface $form,
        FormView $view,
        $jobRepository,
        $translator,
        $datePresenter,
        $localeResolver
    ) {
        $jobRepository->getLastJobExecution($jobInstance, BatchStatus::COMPLETED)
            ->willReturn(null);

        $localeResolver->getCurrentLocale()->shouldNotBeCalled();
        $datePresenter->present(Argument::cetera())->shouldNotBeCalled();
        $translator->trans(
            'pim_connector.export.updated.last_execution.last',
            Argument::cetera()
        )->shouldNotBeCalled();

        $translator->trans('pim_connector.export.updated.last_execution.none')
            ->willReturn('This job has never been exported');

        $this->finishView($view, $form, ['job_instance' => $jobInstance]);
    }

    function it_builds_the_form_view(
        $jobRepository,
        $translator,
        $datePresenter,
        $localeResolver,
        JobExecution $lastExecution,
        FormInterface $form,
        FormView $view,
        JobInstance $jobInstance,
        \DateTime $lastExecutionTime
    ) {
        $jobRepository->getLastJobExecution($jobInstance, BatchStatus::COMPLETED)
            ->willReturn($lastExecution);
        $lastExecution->getStartTime()->willReturn($lastExecutionTime);

        $localeResolver->getCurrentLocale()->willReturn('en_US');

        $datePresenter->present($lastExecutionTime, [
            'locale' => 'en_US'
        ])->willReturn('2016-06-06');

        $translator->trans('pim_connector.export.updated.last_execution.last', [
            '%date%' => '2016-06-06',
        ])->willReturn('Last export: 2016-06-06');

        $this->finishView($view, $form, ['job_instance' => $jobInstance]);
    }

    function it_has_options(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['inherit_data' => true])->shouldBeCalled();
        $resolver->setRequired(['job_instance'])->shouldBeCalled();
        $resolver->setAllowedTypes(['job_instance' => ['Akeneo\Component\Batch\Model\JobInstance']])->shouldBeCalled();
        
        $this->configureOptions($resolver);
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim_updated_since_parameter_type');
    }
}
