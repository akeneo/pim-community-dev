<?php

namespace Oro\Bundle\InstallerBundle\Requirement;

use Symfony\Component\Translation\TranslatorInterface;

use DateTimeZone;

class SettingsRequirements extends RequirementCollection
{
    const REQUIRED_PHP_VERSION = '5.3.8';

    /**
     * @param TranslatorInterface $translator
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function __construct(TranslatorInterface $translator)
    {
        parent::__construct($translator->trans('settings.header', array(), 'requirements'));

        $on  = $translator->trans('switch.on');
        $off = $translator->trans('switch.off');
        $mem = ini_get('memory_limit');

        $this
            ->add(new Requirement(
                $translator->trans('settings.version', array(), 'requirements'),
                version_compare(phpversion(), self::REQUIRED_PHP_VERSION, '>'),
                '>'.self::REQUIRED_PHP_VERSION,
                phpversion()
            ))
            ->add(new Requirement(
                $translator->trans('settings.version_recommended', array(), 'requirements'),
                version_compare(phpversion(), '5.4.11', '>'),
                '>5.4.11',
                phpversion(),
                false
            ))
            ->add(new Requirement(
                $translator->trans('settings.memory_limit', array(), 'requirements'),
                $this->getBytes($mem) >= 256 * 1024 * 1024 || '-1' === $mem,
                '256M',
                ini_get('memory_limit')
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
                !$status = $this->isOn('detect_unicode'),
                $off,
                $this->isOn('detect_unicode') ? $on : $off,
                false
            ))
            ->add(new Requirement(
                'short_open_tag',
                !$status = $this->isOn('short_open_tag'),
                $off,
                $status ? $on : $off,
                false
            ))
            ->add(new Requirement(
                'magic_quotes_gpc',
                !$status = $this->isOn('magic_quotes_gpc'),
                $off,
                $status ? $on : $off,
                false
            ))
            ->add(new Requirement(
                'register_globals',
                !$status = $this->isOn('register_globals'),
                $off,
                $status ? $on : $off,
                false
            ))
            ->add(new Requirement(
                'session.auto_start',
                !$status = $this->isOn('session.auto_start'),
                $off,
                $status ? $on : $off,
                false
            ));

        if (extension_loaded('xdebug')) {
            $this
                ->add(new Requirement(
                    'xdebug.exception',
                    !$status = $this->isOn('xdebug.show_exception_trace'),
                    $off,
                    $status ? $on : $off
                ))
                ->add(new Requirement(
                    'xdebug.scream',
                    !$status = $this->isOn('xdebug.scream'),
                    $off,
                    $status ? $on : $off
                ))
                ->add(new Requirement(
                    $translator->trans('xdebug.max_nesting_level', array(), 'requirements'),
                    ($level = (int) ini_get('xdebug.max_nesting_level')) > 100,
                    '>100',
                    $level,
                    true,
                    $translator->trans('settings.xdebug.max_nesting_level.help', array(), 'requirements')
                ));
        }
    }

    /**
     * @param  string $key Ini setting key
     * @return bool   True if setting switched on, false otherwise
     */
    protected function isOn($key)
    {
        $value = ini_get($key);

        return false != $value && 'off' !== strtolower((string) $value);
    }

    /**
     * @param  string $val
     * @return int
     */
    protected function getBytes($val)
    {
        if (empty($val)) {
            return 0;
        }

        preg_match('/([0-9]+)[\s]*([a-z]*)$/i', trim($val), $matches);

        if (isset($matches[1])) {
            $val = (int) $matches[1];
        }

        switch (strtolower($matches[2])) {
            case 'g':
            case 'gb':
                $val *= 1024;
                // no break
            case 'm':
            case 'mb':
                $val *= 1024;
                // no break
            case 'k':
            case 'kb':
                $val *= 1024;
                // no break
        }

        return (float) $val; // not (int) because of the Windows ]:->
    }
}
