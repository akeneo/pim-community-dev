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
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var DateTimeFormatConverterRegistry
     */
    protected $converterRegistry;

    /**
     * @param TranslatorInterface $translator
     * @param DateTimeFormatConverterRegistry $converterRegistry
     */
    public function __construct(TranslatorInterface $translator, DateTimeFormatConverterRegistry $converterRegistry)
    {
        $this->translator = $translator;
        $this->converterRegistry = $converterRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $jqueryUiFormatter = $this->converterRegistry->getFormatConverter(JqueryUiDateTimeFormatConverter::NAME);
        $dateFormat = $jqueryUiFormatter->getDateFormat(null, $options['date_format']);

        $view->vars['attr']['data-dateformat'] = $dateFormat;

        $view->vars['attr']['placeholder'] = $this->translator->trans('oro.form.click_here_to_select');
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'years'       => range(date('Y') - 120, date('Y')),
                'date_format' => null,
                'format' => function (Options $options) {
                    $intlFormatter = $this->converterRegistry->getFormatConverter(IntlDateTimeFormatConverter::NAME);
                    return $intlFormatter->getDateFormat(null, $options['date_format']);
                },
                'widget'      => 'single_text',
                'attr'        => array(
                    'class' => 'datepicker',
                )
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
