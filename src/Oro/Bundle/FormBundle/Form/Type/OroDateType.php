<?php

namespace Oro\Bundle\FormBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\LocaleBundle\Converter\DateTimeFormatConverterRegistry;
use Oro\Bundle\LocaleBundle\Converter\IntlDateTimeFormatConverter;

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
    public function configureOptions(OptionsResolver $resolver)
    {
        $placeholderDefault = function (Options $options) {
            return $options['required'] ? null : '';
        };

        $resolver->setDefaults(
            [
                'format'      => 'yyyy-MM-dd',
                'widget'      => 'single_text',
                'placeholder' => 'oro.form.click_here_to_select',
            ]
        )->setNormalizer('placeholder', function (Options $options, $placeholder) use ($placeholderDefault) {
            if (is_string($placeholder)) {
                return $placeholder;
            } elseif (is_array($placeholder)) {
                $default = $placeholderDefault($options);

                return array_merge(
                    array('year' => $default, 'month' => $default, 'day' => $default),
                    $placeholder
                );
            }

            return array(
                'year'  => $placeholder,
                'month' => $placeholder,
                'day'   => $placeholder,
            );
        });
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
