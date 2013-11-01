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
     * @var LocaleSettings
     */
    protected $localeSettings;

    /**
     * @param LocaleSettings $localeSettings
     */
    public function __construct(LocaleSettings $localeSettings)
    {
        $this->localeSettings = $localeSettings;
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
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'model_timezone'   => 'UTC',
                'view_timezone'    => 'UTC',
                'date_format'      => 'yyyy-MM-dd HH:mm:ss',
                'widget'           => 'single_text',
                'placeholder'      => 'oro.form.click_here_to_select',
                'localized_format' => true,
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
