<?php

namespace Oro\Bundle\InstallerBundle\Requirement;

use Symfony\Component\Translation\TranslatorInterface;

use ReflectionExtension;

class ExtensionsRequirements extends RequirementCollection
{
    public function __construct(TranslatorInterface $translator)
    {
        parent::__construct($translator->trans('extensions.header', array(), 'requirements'));

        $on  = $translator->trans('switch.on');
        $off = $translator->trans('switch.off');

        $pcreVersion = defined('PCRE_VERSION') ? (float) PCRE_VERSION : null;

        $this
            ->add(new Requirement(
                $translator->trans('extensions.json_encode', array(), 'requirements'),
                $status = function_exists('json_encode'),
                $on,
                $status ? $on : $off,
                true,
                $translator->trans('extensions.help', array('%extension%' => 'JSON'), 'requirements')
            ))
            ->add(new Requirement(
                $translator->trans('extensions.session_start', array(), 'requirements'),
                $status = function_exists('session_start'),
                $on,
                $status ? $on : $off,
                true,
                $translator->trans('extensions.help', array('%extension%' => 'session'), 'requirements')
            ))
            ->add(new Requirement(
                $translator->trans('extensions.token_get_all', array(), 'requirements'),
                $status = function_exists('token_get_all'),
                $on,
                $status ? $on : $off,
                true,
                $translator->trans('extensions.help', array('%extension%' => 'JSON'), 'requirements')
            ))
            ->add(new Requirement(
                $translator->trans('extensions.simplexml_import_dom', array(), 'requirements'),
                $status = function_exists('simplexml_import_dom'),
                $on,
                $status ? $on : $off,
                true,
                $translator->trans('extensions.help', array('%extension%' => 'SimpleXML'), 'requirements')
            ))
            ->add(new Requirement(
                $translator->trans('extensions.apc', array(), 'requirements'),
                !(function_exists('apc_store') && ini_get('apc.enabled')) || version_compare(phpversion('apc'), '3.0.17', '>='),
                '>=3.0.17',
                phpversion('apc'),
                true,
                $translator->trans('extensions.help', array('%extension%' => 'APC (>=3.0.17)'), 'requirements')
            ))
            ->add(new Requirement(
                $translator->trans('extensions.pcre', array(), 'requirements'),
                null !== $pcreVersion && $pcreVersion > 8.0,
                '>=8.0',
                $pcreVersion,
                true,
                $translator->trans('extensions.help', array('%extension%' => 'PCRE (>=8.0)'), 'requirements')
            ))
            ->add(new Requirement(
                $translator->trans('extensions.php_xml', array(), 'requirements'),
                $status = class_exists('DomDocument'),
                $on,
                $status ? $on : $off,
                false,
                $translator->trans('extensions.help', array('%extension%' => 'PHP-XML'), 'requirements')
            ))
            ->add(new Requirement(
                $translator->trans('extensions.mbstring', array(), 'requirements'),
                $status = function_exists('mb_strlen'),
                $on,
                $status ? $on : $off,
                false,
                $translator->trans('extensions.help', array('%extension%' => 'mbstring'), 'requirements')
            ))
            ->add(new Requirement(
                $translator->trans('extensions.iconv', array(), 'requirements'),
                $status = function_exists('iconv'),
                $on,
                $status ? $on : $off,
                false,
                $translator->trans('extensions.help', array('%extension%' => 'iconv'), 'requirements')
            ))
            ->add(new Requirement(
                $translator->trans('extensions.intl', array(), 'requirements'),
                $status = class_exists('Locale'),
                $on,
                $status ? $on : $off,
                false,
                $translator->trans('extensions.help', array('%extension%' => 'intl'), 'requirements')
            ))
        ;

        if (class_exists('Locale')) {
            if (defined('INTL_ICU_VERSION')) {
                $version = INTL_ICU_VERSION;
            } else {
                $reflector = new ReflectionExtension('intl');

                ob_start();
                $reflector->info();
                $output = strip_tags(ob_get_clean());

                preg_match('/^ICU version +(?:=> )?(.*)$/m', $output, $matches);
                $version = $matches[1];
            }

            $this->add(new Requirement(
                $translator->trans('extensions.icu', array(), 'requirements'),
                version_compare($version, '4.0', '>='),
                '4.0',
                $version,
                false,
                $translator->trans('extensions.help', array('%extension%' => 'ICU (>=4.0)'), 'requirements')
            ));
        }

        $status = (function_exists('apc_store') && ini_get('apc.enabled'))
            || function_exists('eaccelerator_put') && ini_get('eaccelerator.enable')
            || function_exists('xcache_set')
        ;

        $this
            ->add(new Requirement(
                $translator->trans('extensions.accelerator.header', array(), 'requirements'),
                $status,
                $on,
                $status ? $on : $off,
                false,
                $translator->trans('extensions.accelerator.help', array(), 'requirements')
            ))
            ->add(new Requirement(
                $translator->trans('extensions.pdo', array(), 'requirements'),
                $status = class_exists('PDO'),
                $on,
                $status ? $on : $off,
                false,
                $translator->trans('extensions.help', array('%extension%' => 'PDO'), 'requirements')
            ))
        ;
    }
}
