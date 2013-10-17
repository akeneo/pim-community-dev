<?php

namespace Oro\Bundle\LocaleBundle\Form\Type;

use Oro\Bundle\LocaleBundle\Provider\LocaleSettingsProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class NameFormatType extends AbstractType
{
    /**
     * @var LocaleSettingsProvider
     */
    protected $settingsProvider;

    /**
     * @param LocaleSettingsProvider $settingsProvider
     */
    public function __construct(LocaleSettingsProvider $settingsProvider)
    {
        $this->settingsProvider = $settingsProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data' => $this->settingsProvider->getNameFormat()
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'text';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_name_format';
    }
}
