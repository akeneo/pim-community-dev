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
use Oro\Bundle\LocaleBundle\Model\LocaleSettings;

class OroDateTimeType extends AbstractType
{
    const NAME = 'oro_datetime';

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var LocaleSettings
     */
    protected $localeSettings;

    /**
     * @var DateTimeFormatConverterRegistry
     */
    protected $converterRegistry;

    /**
     * @param TranslatorInterface $translator
     * @param LocaleSettings $localeSettings
     * @param DateTimeFormatConverterRegistry $converterRegistry
     */
    public function __construct(
        TranslatorInterface $translator,
        LocaleSettings $localeSettings,
        DateTimeFormatConverterRegistry $converterRegistry
    ) {
        $this->translator = $translator;
        $this->localeSettings = $localeSettings;
        $this->converterRegistry = $converterRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $jqueryUiFormatter = $this->converterRegistry->getFormatConverter(JqueryUiDateTimeFormatConverter::NAME);
        $dateFormat = $jqueryUiFormatter->getDateFormat($options['date_format']);
        $timeFormat = $jqueryUiFormatter->getTimeFormat($options['time_format']);

        $view->vars['attr']['data-dateformat'] = $dateFormat;
        $view->vars['attr']['data-timeformat'] = $timeFormat;

        $view->vars['attr']['placeholder'] = $this->translator->trans('oro.form.click_here_to_select');
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'model_timezone'   => 'UTC',
                'view_timezone'    => 'UTC',
                'years'            => range(date('Y') - 120, date('Y')),
                'date_format'      => null,
                'time_format'      => null,
                'localized_format' => true,
                'widget'           => 'single_text',
                'attr'             => array(
                    'class' => 'datetimepicker',
                )
            )
        );

        $resolver->setNormalizers(
            array(
                'view_timezone' => function (Options $options, $value) {
                    if (!empty($options['localized_format'])) {
                        $value = $this->localeSettings->getTimeZone();
                    }
                    return $value;
                },
                'format' => function (Options $options, $value) {
                    if (!empty($options['localized_format'])) {
                        $intlFormatter = $this->converterRegistry->getFormatConverter(
                            IntlDateTimeFormatConverter::NAME
                        );
                        $dateFormat = $intlFormatter->getDateFormat($options['date_format']);
                        $timeFormat = $intlFormatter->getTimeFormat($options['time_format']);
                        $value =  $dateFormat . ' ' . $timeFormat;
                    }
                    return $value;
                }
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
        return self::NAME;
    }
}
