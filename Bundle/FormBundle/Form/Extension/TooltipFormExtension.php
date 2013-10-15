<?php

namespace Oro\Bundle\FormBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TooltipFormExtension extends AbstractTypeExtension
{
    /**
     * @var array
     */
    protected $optionalParameters = array(
        'tooltip',
        'tooltip_details_enabled',
        'tooltip_details_anchor',
        'tooltip_details_link',
        'tooltip_placement'
    );

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setOptional($this->optionalParameters);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        foreach ($this->optionalParameters as $parameter) {
            if (isset($options[$parameter])) {
                $view->vars[$parameter] = $options[$parameter];
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return 'form';
    }
}
