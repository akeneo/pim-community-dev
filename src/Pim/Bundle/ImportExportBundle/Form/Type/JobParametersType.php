<?php

namespace Pim\Bundle\ImportExportBundle\Form\Type;

use Akeneo\Bundle\BatchBundle\Connector\ConnectorRegistry;
use Akeneo\Component\Batch\Job\JobConfiguratorRegistry;
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
    /** @var JobConfiguratorRegistry */
    protected $jobConfiguratorRegistry;

    /** @var ContainerInterface */
    private $container;

    /** @var string */
    protected $jobParamsClass;

    /**
     * @param JobConfiguratorRegistry $constraintProviderRegistry
     * @param ContainerInterface      $container
     * @param string                  $jobParamsClass
     */
    public function __construct(
        JobConfiguratorRegistry $jobConfiguratorRegistry,
        ContainerInterface $container,
        $jobParamsClass
    ) {
        $this->jobConfiguratorRegistry = $jobConfiguratorRegistry;
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
        $jobConfiguratorRegistry = $this->jobConfiguratorRegistry;

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($factory, $jobConfiguratorRegistry) {
                $form   = $event->getForm();
                $jobInstance = $form->getRoot()->getData();
                if (null == $jobInstance->getId()) {
                    return;
                }
                $job = $this->getConnectorRegistry()->getJob($jobInstance);

                $resolver = new OptionsResolver();
                $resolver->setRequired('fields');

                foreach ($jobConfiguratorRegistry->getConfiguratorsForJob($job) as $configurator) {
                    $configurator->configure($resolver);
                }

                $options = $resolver->resolve([]);
                $fields = $options['fields'];

                foreach ($fields as $field) {
                    if (isset($field['system']) && true === $field['system']) {
                        continue;
                    }

                    $form->add($factory->createNamed($field['name'], $field['type'], null, $field['options']));
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
    protected final function getConnectorRegistry()
    {
        return $this->container->get('akeneo_batch.connectors');
    }
}
