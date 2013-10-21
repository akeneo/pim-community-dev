<?php

namespace Oro\Bundle\LocaleBundle\Twig;

use Oro\Bundle\LocaleBundle\Model\LocaleSettings;

class LocaleExtension extends \Twig_Extension
{
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
    public function getName()
    {
        return 'oro_locale';
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('oro_locale', array($this, 'getLocale')),
            new \Twig_SimpleFunction('oro_timezone', array($this, 'getTimeZone')),
        );
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->localeSettings->getLocale();
    }

    /**
     * @return string
     */
    public function getTimeZone()
    {
        $date = new \DateTime('now', new \DateTimeZone($this->localeSettings->getTimeZone()));
        return $date->format('P');
    }
}
