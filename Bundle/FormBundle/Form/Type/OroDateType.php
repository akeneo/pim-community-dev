<?php

namespace Oro\Bundle\FormBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\LocaleBundle\Converter\DateTimeFormatConverterRegistry;
use Oro\Bundle\LocaleBundle\Converter\IntlDateTimeFormatConverter;
use Oro\Bundle\UIBundle\Converter\JqueryUiDateTimeFormatConverter;

class OroDateType extends AbstractType
{
    const NAME = 'oro_date';

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['placeholder'] = $options['placeholder'];
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'model_timezone' => 'UTC',
                'view_timezone'  => 'UTC',
                'format'         => 'yyyy-MM-dd', // ISO format
                'widget'         => 'single_text',
                'placeholder'    => 'oro.form.click_here_to_select',
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'date';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
