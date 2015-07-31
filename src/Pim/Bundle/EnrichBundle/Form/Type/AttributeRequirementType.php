<?php

namespace Pim\Bundle\EnrichBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Attribute requirement form type
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeRequirementType extends AbstractType
{
    /** @var string */
    protected $dataClass;

    /**
     * @param string $dataClass
     */
    public function __construct($dataClass)
    {
        $this->dataClass = $dataClass;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['keep_non_required']) {
            // Hidden value is used to store 1 or 0 and send the "uncheck" value
            // (which is impossible to do with a checkbox)
            $builder->add('required', 'hidden');
        } else {
            $builder->add('required', 'checkbox');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view['required']->vars['keep_non_required'] = $options['keep_non_required'];
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'        => $this->dataClass,
                'keep_non_required' => false,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_enrich_attribute_requirement';
    }
}
