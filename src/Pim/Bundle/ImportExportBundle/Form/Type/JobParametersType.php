<?php

namespace Pim\Bundle\ImportExportBundle\Form\Type;

use Akeneo\Component\Batch\Job\JobParameters;
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
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setDataMapper($this);
        $factory = $builder->getFormFactory();
        $registry = new JobParameterFieldsRegistry();
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($factory, $registry) {
                $form   = $event->getForm();
                $jobInstance = $form->getRoot()->getData();
                if (null == $jobInstance->getId()) {
                    return;
                }
                $configs = $registry->getFields($jobInstance->getJob());
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
