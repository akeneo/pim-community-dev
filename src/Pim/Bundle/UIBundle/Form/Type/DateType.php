<?php

namespace Pim\Bundle\UIBundle\Form\Type;

use Pim\Component\Localization\LocaleResolver;
use Pim\Component\Localization\Validator\Constraints\DateFormat;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * PIM date type
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateType extends AbstractType
{
    /** @var LocaleResolver */
    protected $localeResolver;

    /**
     * @param LocaleResolver $localeResolver
     */
    public function __construct(LocaleResolver $localeResolver)
    {
        $this->localeResolver = $localeResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $placeholderDefault = function (Options $options) {
            return $options['required'] ? null : '';
        };

        $constraint = new DateFormat();

        $localeOptions = $this->localeResolver->getFormats();

        $resolver->setDefaults(
            [
                'widget'                     => 'single_text',
                'placeholder'                => 'oro.form.click_here_to_select',
                'invalid_message'            => $constraint->message,
                'invalid_message_parameters' => ['{{ date_format }}' => $localeOptions['date_format']],
                'locale_options'             => $localeOptions,
                'format'                     => $localeOptions['date_format']
            ]
        )->setNormalizer('placeholder', function (Options $options, $placeholder) use ($placeholderDefault) {
            if (is_string($placeholder)) {
                return $placeholder;
            } elseif (is_array($placeholder)) {
                $default = $placeholderDefault($options);

                return array_merge(
                    ['year' => $default, 'month' => $default, 'day' => $default],
                    $placeholder
                );
            }

            return [
                'year'  => $placeholder,
                'month' => $placeholder,
                'day'   => $placeholder,
            ];
        });
    }

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
    public function getParent()
    {
        return 'date';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_date';
    }
}
