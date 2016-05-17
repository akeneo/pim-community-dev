<?php

namespace Pim\Bundle\ImportExportBundle\Form\Type;

use Akeneo\Bundle\BatchBundle\Connector\ConnectorRegistry;
use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Job\JobParameters\ConstraintCollectionProviderRegistry;
use Pim\Bundle\ImportExportBundle\JobParameters\FormConfigurationProviderRegistry;
use Symfony\Component\DependencyInjection\ContainerInterface;
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

    /** @var ContainerInterface */
    private $container;

    /** @var string */
    protected $jobParamsClass;

    /**
     * @param FormConfigurationProviderRegistry    $configProviderRegistry
     * @param ConstraintCollectionProviderRegistry $constraintProviderRegistry
     * @param ContainerInterface                   $container
     * @param string                               $jobParamsClass
     */
    public function __construct(
        FormConfigurationProviderRegistry $configProviderRegistry,
        ConstraintCollectionProviderRegistry $constraintProviderRegistry,
        ContainerInterface $container,
        $jobParamsClass
    ) {
        $this->configProviderRegistry = $configProviderRegistry;
        $this->constraintProviderRegistry = $constraintProviderRegistry;
        $this->container = $container;
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
                $job = $this->getConnectorRegistry()->getJob($jobInstance);
                $configProvider = $configProviderRegistry->get($job);
                $configs = $configProvider->getFormConfiguration();
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

    /**
     * Should be changed with TIP-418, here we work around a circular reference due to the way we instanciate the whole
     * Job classes in the DIC
     *
     * @return ConnectorRegistry
     */
    final protected function getConnectorRegistry()
    {
        return $this->container->get('akeneo_batch.connectors');
    }
}
