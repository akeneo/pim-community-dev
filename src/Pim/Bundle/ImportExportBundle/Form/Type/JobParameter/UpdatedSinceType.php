<?php

namespace Pim\Bundle\ImportExportBundle\Form\Type\JobParameter;

use Akeneo\Component\Batch\Job\BatchStatus;
use Akeneo\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\Localization\Factory\DateFactory;
use Akeneo\Component\Localization\Presenter\PresenterInterface;
use Pim\Bundle\EnrichBundle\Resolver\LocaleResolver;
use Pim\Bundle\ImportExportBundle\Constraints\UpdatedSinceStrategy;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Updated time condition type use in export builder
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdatedSinceType extends AbstractType
{
    /** @var JobRepositoryInterface */
    protected $jobRepository;
    
    /** @var TranslatorInterface */
    protected $translator;
    
    /** @var LocaleResolver */
    protected $localeResolver;
    
    /** @var PresenterInterface */
    protected $datePresenter;

    /** @var DateFactory */
    protected $dateFactory;

    /** @var string */
    protected $jobInstanceClass;

    /**
     * @param JobRepositoryInterface $jobRepository
     * @param TranslatorInterface    $translator
     * @param LocaleResolver         $localeResolver
     * @param PresenterInterface     $datePresenter
     * @param DateFactory            $dateFactory
     * @param string                 $jobInstanceClass
     */
    public function __construct(
        JobRepositoryInterface $jobRepository,
        TranslatorInterface $translator,
        LocaleResolver $localeResolver,
        PresenterInterface $datePresenter,
        DateFactory $dateFactory,
        $jobInstanceClass
    ) {
        $this->jobRepository = $jobRepository;
        $this->translator = $translator;
        $this->localeResolver = $localeResolver;
        $this->datePresenter = $datePresenter;
        $this->dateFactory = $dateFactory;
        $this->jobInstanceClass = $jobInstanceClass;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $dateFormatter = $this->dateFactory->create([
            'locale' => $this->localeResolver->getCurrentLocale(),
        ]);

        $builder
            ->add('updated_since_strategy', 'choice', [
                'choices' => [
                    'all'          => 'pim_connector.export.updated.updated_since_strategy.choice.all',
                    'last_export'  => 'pim_connector.export.updated.updated_since_strategy.choice.last_export',
                    'since_date'   => 'pim_connector.export.updated.updated_since_strategy.choice.since_date',
                    'since_period' => 'pim_connector.export.updated.updated_since_strategy.choice.since_period',
                ],
                'select2'  => true,
            ])
            ->add('updated_since_date', 'datetime', [
                'widget' => 'single_text',
                'format' => $dateFormatter->getPattern(),
                'input'  => 'string',
                'constraints' => new UpdatedSinceStrategy([
                    'jobInstance' => $options['job_instance'],
                    'strategy' => 'since_date',
                ])
            ])
            ->add('updated_since_period', 'number', [
                'constraints' => new UpdatedSinceStrategy([
                    'jobInstance' => $options['job_instance'],
                    'strategy' => 'since_period',
                ])
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['info'] = $this->getLastExecution($options['job_instance']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['inherit_data' => true]);
        $resolver->setRequired(['job_instance']);
        $resolver->setAllowedTypes(['job_instance' => [$this->jobInstanceClass]]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_updated_since_parameter_type';
    }
    
    /**
     * Get last execution job
     *
     * @param JobInstance $jobInstance
     *
     * @return array
     */
    protected function getLastExecution(JobInstance $jobInstance)
    {
        $lastExecution = $this->jobRepository->getLastJobExecution($jobInstance, BatchStatus::COMPLETED);

        if (null === $lastExecution) {
            return $this->translator->trans('pim_connector.export.updated.last_execution.none');
        }

        $lastExecutionInfo = $this->translator->trans('pim_connector.export.updated.last_execution.last', [
            '%date%' => $this->datePresenter->present($lastExecution->getStartTime(), [
                'locale' => $this->localeResolver->getCurrentLocale(),
            ])
        ]);

        return $lastExecutionInfo;
    }
}
