<?php

namespace Oro\Bundle\InstallerBundle\Requirement;

use Symfony\Component\Translation\TranslatorInterface;

use DateTimeZone;

class SettingsRequirements extends RequirementCollection
{
    const REQUIRED_PHP_VERSION = '5.3.3';

    public function __construct(TranslatorInterface $translator)
    {
        parent::__construct($translator->trans('settings.header', array(), 'requirements'));

        $on  = $translator->trans('switch.on');
        $off = $translator->trans('switch.off');

        $this
            ->add(new Requirement(
                $translator->trans('settings.version', array(), 'requirements'),
                version_compare(phpversion(), self::REQUIRED_PHP_VERSION, '>='),
                '>='.self::REQUIRED_PHP_VERSION,
                phpversion()
            ))
            ->add(new Requirement(
                $translator->trans('settings.version_recommended', array(), 'requirements'),
                version_compare(phpversion(), '5.3.8', '>='),
                '>=5.3.8',
                phpversion(),
                false
            ))
            ->add(new Requirement(
                $translator->trans('settings.timezone', array(), 'requirements'),
                $this->isOn('date.timezone'),
                $translator->trans('settings.any', array(), 'requirements'),
                ini_get('date.timezone')
            ));

        if (version_compare(phpversion(), self::REQUIRED_PHP_VERSION, '>=')) {
            $this->add(new Requirement(
                $translator->trans('settings.timezone_deprecated.header', array(), 'requirements'),
                in_array(date_default_timezone_get(), DateTimeZone::listIdentifiers()),
                $translator->trans('settings.non_deprecated', array(), 'requirements'),
                date_default_timezone_get(),
                true,
                $translator->trans('settings.timezone_deprecated.help', array('%timezone%' => date_default_timezone_get()), 'requirements')
            ));
        }

        $this
            ->add(new Requirement(
                'detect_unicode',
                !$this->isOn('detect_unicode'),
                $on,
                ini_get('detect_unicode'),
                false
            ))
            ->add(new Requirement(
                'short_open_tag',
                !$this->isOn('short_open_tag'),
                $off,
                ini_get('short_open_tag'),
                false
            ))
            ->add(new Requirement(
                'magic_quotes_gpc',
                !$this->isOn('magic_quotes_gpc'),
                $off,
                ini_get('magic_quotes_gpc'),
                false
            ))
            ->add(new Requirement(
                'register_globals',
                !$this->isOn('register_globals'),
                $off,
                ini_get('register_globals'),
                false
            ))
            ->add(new Requirement(
                'session.auto_start',
                !$this->isOn('session.auto_start'),
                $off,
                ini_get('session.auto_start'),
                false
            ));
    }

    private function isOn($key)
    {
        $value = ini_get($key);

        return false != $value && 'off' !== $value;
    }
}
