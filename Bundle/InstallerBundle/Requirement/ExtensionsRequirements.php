<?php

namespace Oro\Bundle\InstallerBundle\Requirement;

use Symfony\Component\Translation\TranslatorInterface;

use ReflectionExtension;

class ExtensionsRequirements extends RequirementCollection
{
    /**
     * @param TranslatorInterface $translator
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function __construct(TranslatorInterface $translator)
    {
        parent::__construct($translator->trans('extensions.header', array(), 'requirements'));

        $on  = $translator->trans('switch.on');
        $off = $translator->trans('switch.off');

        $pcreVersion = defined('PCRE_VERSION') ? (float) PCRE_VERSION : null;
        $gdVersion   = defined('GD_VERSION') ? (float) GD_VERSION : null;
        $curlVersion = function_exists('curl_version') ? curl_version() : null;
        $apcVersion  = phpversion('apc');

        $this
            ->add(new Requirement(
                $translator->trans('extensions.ctype', array(), 'requirements'),
                $status = function_exists('ctype_alpha'),
                $on,
                $status ? $on : $off,
                true,
                $translator->trans('extensions.help', array('%extension%' => 'ctype'), 'requirements')
            ))
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
                $translator->trans('extensions.help', array('%extension%' => 'Tokenizer'), 'requirements')
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
                function_exists('apc_store') && ini_get('apc.enabled') && version_compare($apcVersion, '3.0.17', '>='),
                '>=3.0.17',
                $apcVersion ? $apcVersion : $off,
                false,
                $translator->trans('extensions.help', array('%extension%' => 'APC (>=3.0.17)'), 'requirements')
            ))
            ->add(new Requirement(
                $translator->trans('extensions.pcre', array(), 'requirements'),
                null !== $pcreVersion && $pcreVersion >= 8.0,
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
            ));

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

        $status =
            (extension_loaded('eaccelerator') && ini_get('eaccelerator.enable'))
            ||
            (extension_loaded('apc') && ini_get('apc.enabled'))
            ||
            (extension_loaded('Zend Optimizer+') && ini_get('zend_optimizerplus.enable'))
            ||
            (extension_loaded('Zend OPcache') && ini_get('opcache.enable'))
            ||
            (extension_loaded('xcache') && ini_get('xcache.cacher'))
            ||
            (extension_loaded('wincache') && ini_get('wincache.ocenabled'));

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
            ->add(new Requirement(
                $translator->trans('extensions.gd', array(), 'requirements'),
                null !== $gdVersion && $gdVersion >= 2.0,
                '>=2.0',
                $gdVersion,
                true,
                $translator->trans('extensions.help', array('%extension%' => 'GD (>=2.0)'), 'requirements')
            ))
            ->add(new Requirement(
                $translator->trans('extensions.soap', array(), 'requirements'),
                $status = class_exists('SoapClient'),
                $on,
                $status ? $on : $off,
                false,
                $translator->trans('extensions.help', array('%extension%' => 'SOAP'), 'requirements')
            ))
            ->add(new Requirement(
                $translator->trans('extensions.curl', array(), 'requirements'),
                null !== $curlVersion && (float) $curlVersion['version'] >= 7.0,
                '>=7.0',
                null !== $curlVersion ? (float) $curlVersion['version'] : '',
                false,
                $translator->trans('extensions.help', array('%extension%' => 'cURL (>=7.0)'), 'requirements')
            ))
            ->add(new Requirement(
                $translator->trans('extensions.mcrypt', array(), 'requirements'),
                $status = function_exists('mcrypt_encrypt'),
                $on,
                $status ? $on : $off,
                true,
                $translator->trans('extensions.help', array('%extension%' => 'Mcrypt'), 'requirements')
            ));

        // Windows specific checks
        if (defined('PHP_WINDOWS_VERSION_BUILD')) {
            $this
                ->add(new Requirement(
                    $translator->trans('extensions.fileinfo', array(), 'requirements'),
                    $status = function_exists('finfo_open'),
                    $on,
                    $status ? $on : $off,
                    false,
                    $translator->trans('extensions.help', array('%extension%' => 'Fileinfo'), 'requirements')
                ))
                ->add(new Requirement(
                    $translator->trans('extensions.com', array(), 'requirements'),
                    $status = class_exists('COM'),
                    $on,
                    $status ? $on : $off,
                    false,
                    $translator->trans('extensions.help', array('%extension%' => 'COM'), 'requirements')
                ));
        }
    }
}
