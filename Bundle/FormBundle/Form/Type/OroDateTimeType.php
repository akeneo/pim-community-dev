<?php

namespace Oro\Bundle\FormBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

class OroDateTimeType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $dateFormat = is_int($options['format']) ? $options['format'] : \IntlDateFormatter::SHORT;
        $calendar   = \IntlDateFormatter::GREGORIAN;
        $pattern    = is_string($options['format']) ? $options['format'] : null;

        $formatter_date  = new \IntlDateFormatter(
            \Locale::getDefault(),
            $dateFormat,
            \IntlDateFormatter::NONE,
            'UTC',
            $calendar,
            $pattern
        );

        $formatter_time  = new \IntlDateFormatter(
            \Locale::getDefault(),
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::SHORT,
            'UTC',
            $calendar,
            $pattern
        );

        $view->vars['attr']['data-dateformat'] = str_replace(
            array('M', 'yy'),
            array('m', 'y'),
            $formatter_date->getPattern()
        );
        $view->vars['attr']['data-timeformat'] = str_replace(
            array('a', 'h'),
            array('tt', 'hh'),
            $formatter_time->getPattern()
        );

        $view->vars['attr']['placeholder'] =
            $view->vars['attr']['data-dateformat'] . ' ' . $view->vars['attr']['data-timeformat'];
    }


    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'years'     => range(date('Y') - 120, date('Y')),
                'format'    => \IntlDateFormatter::SHORT,
                'widget'    => 'single_text',
                'attr'      => array(
                    'class' => 'datetimepicker',
                )
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'datetime';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_datetime';
    }
}
