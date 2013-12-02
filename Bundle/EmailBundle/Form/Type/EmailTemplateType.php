<?php

namespace Oro\Bundle\EmailBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Oro\Bundle\EmailBundle\Entity\EmailTemplate;

class EmailTemplateType extends AbstractType
{
    /**
     * @var array
     */
    protected $entityNameChoices = array();

    /**
     * @param array $entitiesConfig
     */
    public function __construct($entitiesConfig = array())
    {
        $this->entityNameChoices = array_map(
            function ($value) {
                return isset($value['name'])? $value['name'] : '';
            },
            $entitiesConfig
        );
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'entityName',
            'choice',
            array(
                'choices'            => $this->entityNameChoices,
                'multiple'           => false,
                'translation_domain' => 'config',
                'empty_value'        => '',
                'empty_data'         => null,
                'required'           => true
            )
        );

        $builder->add(
            'name',
            'text',
            array(
                'required' => true
            )
        );

        $builder->add(
            'type',
            'choice',
            array(
                'multiple'           => false,
                'expanded'           => true,
                'choices'            => array(
                    'html' => 'oro.email.datagrid.emailtemplate.filter.type.html',
                    'txt'  => 'oro.email.datagrid.emailtemplate.filter.type.txt'
                ),
                'required'           => true
            )
        );

        $builder->add(
            'translations',
            'oro_email_emailtemplate_translatation',
            array(
                'required' => false
            )
        );

        $builder->add(
            'parentTemplate',
            'hidden',
            array(
                'property_path' => 'parent'
            )
        );

        // disable some fields for non editable email template
        $setDisabled = function (&$options) {
            if (isset($options['auto_initialize'])) {
                $options['auto_initialize'] = false;
            }
            $options['disabled'] = true;
        };
        $factory = $builder->getFormFactory();
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($factory, $setDisabled) {
                $data = $event->getData();
                if ($data && $data->getId() && $data->getIsSystem()) {
                    $form = $event->getForm();
                    // entityName field
                    $options = $form->get('entityName')->getConfig()->getOptions();
                    $setDisabled($options);
                    $form->add($factory->createNamed('entityName', 'choice', null, $options));
                    // name field
                    $options = $form->get('name')->getConfig()->getOptions();
                    $setDisabled($options);
                    $form->add($factory->createNamed('name', 'text', null, $options));
                    if (!$data->getIsEditable()) {
                        // name field
                        $options = $form->get('type')->getConfig()->getOptions();
                        $setDisabled($options);
                        $form->add($factory->createNamed('type', 'choice', null, $options));
                    }
                }
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class'           => 'Oro\Bundle\EmailBundle\Entity\EmailTemplate',
                'intention'            => 'emailtemplate',
                'extra_fields_message' => 'This form should not contain extra fields: "{{ extra_fields }}"',
                'cascade_validation'   => true,
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_email_emailtemplate';
    }
}
