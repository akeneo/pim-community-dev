<?php

namespace Oro\Bundle\EmailBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

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
                'required'           => true,
                'translation_domain' => 'datagrid'
            )
        );

        $builder->add(
            'translations',
            'oro_email_emailtemplate_translatation',
            array(
                'required' => true
            )
        );

        $builder->add(
            'parent',
            'hidden'
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
