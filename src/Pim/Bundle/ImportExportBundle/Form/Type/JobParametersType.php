<?php

namespace Pim\Bundle\ImportExportBundle\Form\Type;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Job\JobParameters\ConstraintCollectionProviderRegistry;
use Akeneo\Component\Batch\Job\JobRegistry;
use Pim\Bundle\ImportExportBundle\JobParameters\FormConfigurationProviderRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Job parameters form type, JobParameters is immutable to we use a DataMapperInterface to create a new
 * JobParameters with the fulfilled configuration
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobParametersType extends AbstractType implements DataMapperInterface
{
    /** @var FormConfigurationProviderRegistry */
    protected $configProviderRegistry;

    /** @var ConstraintCollectionProviderRegistry */
    protected $constraintProviderRegistry;

    /** @var JobRegistry */
    protected $jobRegistry;

    /** @var string */
    protected $jobParamsClass;

    /**
     * @param FormConfigurationProviderRegistry    $configProviderRegistry
     * @param ConstraintCollectionProviderRegistry $constraintProviderRegistry
     * @param JobRegistry                          $jobRegistry
     * @param string                               $jobParamsClass
     */
    public function __construct(
        FormConfigurationProviderRegistry $configProviderRegistry,
        ConstraintCollectionProviderRegistry $constraintProviderRegistry,
        JobRegistry $jobRegistry,
        $jobParamsClass
    ) {
        $this->configProviderRegistry = $configProviderRegistry;
        $this->constraintProviderRegistry = $constraintProviderRegistry;
        $this->jobRegistry = $jobRegistry;
        $this->jobParamsClass = $jobParamsClass;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setDataMapper($this);
        $factory = $builder->getFormFactory();
        $configProviderRegistry = $this->configProviderRegistry;
        $constraintProviderRegistry = $this->constraintProviderRegistry;
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($factory, $configProviderRegistry, $constraintProviderRegistry) {
                $form   = $event->getForm();
                $jobInstance = $form->getRoot()->getData();
                if (null == $jobInstance->getId()) {
                    return;
                }
                $job = $this->jobRegistry->get($jobInstance->getAlias());
                $configProvider = $configProviderRegistry->get($job);
                $configs = $configProvider->getFormConfiguration($jobInstance);
                $constraintProvider = $constraintProviderRegistry->get($job);
                $collection = $constraintProvider->getConstraintCollection();
                $fieldConstraints = $collection->fields;

                foreach ($configs as $parameter => $config) {
                    if (isset($config['system']) && true === $config['system']) {
                        continue;
                    }
                    $config = array_merge(
                        [
                            'type'    => 'text',
                            'options' => [],
                        ],
                        $config
                    );
                    $options = array_merge(
                        [
                            'auto_initialize' => false,
                            'required'        => false,
                            'label'           => ucfirst($parameter),
                        ],
                        $config['options']
                    );

                    if (isset($fieldConstraints[$parameter])) {
                        $options['constraints'] = $fieldConstraints[$parameter]->constraints;
                    }

                    $form->add($factory->createNamed($parameter, $config['type'], null, $options));
                }
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => $this->jobParamsClass,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function mapDataToForms($data, $forms)
    {
        foreach ($forms as $form) {
            $form->setData($data->get($form->getName()));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function mapFormsToData($forms, &$data)
    {
        $parameters = [];
        foreach ($forms as $form) {
            $parameters[$form->getName()] = $form->getData();
        }
        $data = new $this->jobParamsClass($parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_import_export_job_parameters';
    }
}
