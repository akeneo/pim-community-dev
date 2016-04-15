<?php

namespace Pim\Bundle\ImportExportBundle\Form\Type;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Job\JobParameters\ConstraintsRegistry;
use Pim\Bundle\ImportExportBundle\Form\Type\JobParameters\FormsOptionsRegistry;
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
    /** @var FormsOptionsRegistry */
    protected $formsOptionsRegistry;

    /** @var FormsOptionsRegistry */
    protected $constraintsRegistry;

    /**
     * @param FormsOptionsRegistry $formsOptionsRegistry
     */
    public function __construct(FormsOptionsRegistry $formsOptionsRegistry, ConstraintsRegistry $constraintsRegistry)
    {
        $this->formsOptionsRegistry = $formsOptionsRegistry;
        $this->constraintsRegistry = $constraintsRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setDataMapper($this);
        $factory = $builder->getFormFactory();
        $formOptionsRegistry = $this->formsOptionsRegistry;
        // TODO: constraints registry could be injected in the form options registry?
        // TODO: re-work the following form options building (copy/pasted from dropped ConfigurationFormType)
        // TODO: move the whole FormType, DataTransformer, Registry in the EnrichBundle to prepare the drop of
        //       ImportExportBundle
        $constraintsRegistry = $this->constraintsRegistry;
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($factory, $formOptionsRegistry, $constraintsRegistry) {
                $form   = $event->getForm();
                $jobInstance = $form->getRoot()->getData();
                if (null == $jobInstance->getId()) {
                    return;
                }
                $formOptions = $formOptionsRegistry->getFormsOptions($jobInstance->getJob());
                $configs = $formOptions->getOptions();

                $job = $jobInstance->getJob();
                $constraints = $constraintsRegistry->getConstraints($job);
                $fieldConstraints = [];
                if (!$constraints instanceof JobParameters\EmptyConstraints) {
                    $collection = $constraints->getConstraints();
                    $fieldConstraints = $collection->fields;
                }

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
                'data_class' => 'Akeneo\\Component\\Batch\\Job\\JobParameters',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function mapDataToForms($data, $forms)
    {
        foreach ($forms as $form) {
            $form->setData($data->getParameter($form->getName()));
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
        $data = new JobParameters($parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_import_export_job_parameters';
    }
}
