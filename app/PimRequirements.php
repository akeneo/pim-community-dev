<?php

require_once __DIR__ . '/../var/SymfonyRequirements.php';

use Symfony\Component\Intl\Intl;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Yaml\Yaml;

/**
 * Akeneo PIM requirements
 *
 * This class specifies all requirements and optional recommendations that are necessary
 * to install and run Akeneo PIM application
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimRequirements extends SymfonyRequirements
{
    const REQUIRED_PHP_VERSION = '7.1.0';
    const REQUIRED_GD_VERSION = '2.0';
    const REQUIRED_CURL_VERSION = '7.0';
    const REQUIRED_ICU_VERSION = '4.2';
    const LOWEST_REQUIRED_MYSQL_VERSION = '5.7.0';
    const GREATEST_REQUIRED_MYSQL_VERSION = '5.8.0';

    const REQUIRED_EXTENSIONS = [
        'apcu',
        'bcmath',
        'curl',
        'gd',
        'intl',
        'mcrypt',
        'pdo_mysql',
        'soap',
        'xml',
        'zip',
        'exif',
        'imagick'
    ];

    /**
     * {@inheritdoc}
     */
    public function __construct(array $directoriesToCheck = [])
    {
        parent::__construct();

        $phpVersion  = phpversion();
        $gdVersion   = defined('GD_VERSION') ? GD_VERSION : null;
        $curlVersion = function_exists('curl_version') ? curl_version() : null;
        $icuVersion  = Intl::getIcuVersion();

        $this->addPimRequirement(
            version_compare($phpVersion, self::REQUIRED_PHP_VERSION, '>='),
            sprintf('PHP version must be at least %s (%s installed)', self::REQUIRED_PHP_VERSION, $phpVersion),
            sprintf('You are running PHP version "<strong>%s</strong>", but needs at least PHP "<strong>%s</strong>" to run.
                Before using, upgrade your PHP installation, preferably to the latest version.',
                $phpVersion, self::REQUIRED_PHP_VERSION),
            sprintf('Install PHP %s or newer (installed version is %s)', self::REQUIRED_PHP_VERSION, $phpVersion)
        );

        foreach (self::REQUIRED_EXTENSIONS as $requiredExtension) {
            $this->addPimRequirement(
                extension_loaded($requiredExtension),
                sprintf('%s extension should be available', $requiredExtension),
                sprintf('Install and enable the <strong>%s</strong> extension.', $requiredExtension)
            );
        }

        $this->addPimRequirement(
            null !== $gdVersion && version_compare($gdVersion, self::REQUIRED_GD_VERSION, '>='),
            'GD extension must be at least ' . self::REQUIRED_GD_VERSION,
            'Install and enable the <strong>GD</strong> extension at least ' . self::REQUIRED_GD_VERSION . ' version'
        );

        $this->addPimRequirement(
            null !== $icuVersion && version_compare($icuVersion, self::REQUIRED_ICU_VERSION, '>='),
            'icu library must be at least ' . self::REQUIRED_ICU_VERSION,
            'Install and enable the <strong>icu</strong> library at least ' . self::REQUIRED_ICU_VERSION . ' version'
        );

        $this->addRecommendation(
            null !== $curlVersion && version_compare($curlVersion['version'], self::REQUIRED_CURL_VERSION, '>='),
            'cURL extension must be at least ' . self::REQUIRED_CURL_VERSION,
            'Install and enable the <strong>cURL</strong> extension at least ' . self::REQUIRED_CURL_VERSION . ' version'
        );

        // Windows specific checks
        if (defined('PHP_WINDOWS_VERSION_BUILD')) {
            $this->addRecommendation(
                function_exists('finfo_open'),
                'finfo_open() should be available',
                'Install and enable the <strong>Fileinfo</strong> extension.'
            );

            $this->addRecommendation(
                class_exists('COM'),
                'COM extension should be installed',
                'Install and enable the <strong>COM</strong> extension.'
            );
        }

        $baseDir = realpath(__DIR__ . '/..');
        $mem     = $this->getBytes(ini_get('memory_limit'));

        $this->addPhpIniRequirement(
            'memory_limit',
            function ($cfgValue) use ($mem) {
                return $mem >= 512 * 1024 * 1024 || -1 == $mem;
            },
            false,
            'memory_limit should be at least 512M',
            'Set the "<strong>memory_limit</strong>" setting in php.ini<a href="#phpini">*</a> to at least "512M".'
        );

        $directories = array(
            'web/bundles'
        );
        foreach ($directories as $directory) {
            $this->addPimRequirement(
                is_writable($baseDir.'/'.$directory),
                $directory.' directory must be writable',
                'Change the permissions of the "<strong>'.$directory.'</strong>" directory so that the web server can write into it.'
            );
        }

        $currentMySQLVersion = $this->getMySQLVersion();

        $this->addPimRequirement(
            version_compare($currentMySQLVersion, self::LOWEST_REQUIRED_MYSQL_VERSION, '>=')  &&
            version_compare($currentMySQLVersion, self::GREATEST_REQUIRED_MYSQL_VERSION, '<'),
            sprintf(
                'MySQL version must be greater or equal to %s and lower than %s',
                self::LOWEST_REQUIRED_MYSQL_VERSION,
                self::GREATEST_REQUIRED_MYSQL_VERSION
            ),
            sprintf(
                'Install MySQL greater or equal to %s and lower than %s (installed version is %s)',
                self::LOWEST_REQUIRED_MYSQL_VERSION,
                self::GREATEST_REQUIRED_MYSQL_VERSION,
                $currentMySQLVersion
            )
        );

        $this->addPimRequirement(
            function_exists('exec'),
            'The exec() function should be enabled in order to run jobs',
            'Make sure the <strong>exec()</strong> function is not disabled in php.ini'
        );

        $this->addRecommendation(
            ini_get('apc.enable_cli'),
            'APCu should be enabled in CLI to get better performances',
            'Set <strong>apc.enable_cli</strong> to <strong>1</strong>'
        );

        // Check directories
        foreach ($directoriesToCheck as $directoryToCheck) {
            $this->addPimRequirement(
                is_writable($directoryToCheck),
                sprintf('%s directory must be writable', $directoryToCheck),
                sprintf('Change the permissions of the "<strong>%s</strong>" directory', $directoryToCheck)
            );
        }
    }

    /**
     * Adds an Akeneo PIM specific requirement
     *
     * @param bool        $fulFilled   Whether the requirement is fulfilled
     * @param string      $testMessage The message for testing the requirement
     * @param string      $helpHtml    The help text formatted in HTML for resolving the problem
     * @param string|null $helpText    The help text
     *                                 (when null, it will be inferred from $helpHtml, i.e. stripped from HTML tags)
     */
    public function addPimRequirement($fulFilled, $testMessage, $helpHtml, $helpText = null)
    {
        $this->add(new PimRequirement($fulFilled, $testMessage, $helpHtml, $helpText, false));
    }

    /**
     * Get the list of Akeneo PIM specific requirements
     *
     * @return array
     */
    public function getPimRequirements()
    {
        return array_filter($this->getRequirements(), function ($requirement) {
            return $requirement instanceof PimRequirement;
        });
    }

    /**
     * Gets the MySQL server version thanks to a PDO connection.
     *
     * If no connection is reached, or that "parameters.yml" do not exists, an
     * exception is thrown, then catch. If "parameters_test.yml" do not exists
     * either, then the exception is thrown again.
     * If it exits, an attempt to connect is done, and can result in an exception
     * if no connection is reached.
     *
     * @return string
     */
    protected function getMySQLVersion()
    {
        $file = file_get_contents(__DIR__.'/config/parameters.yml');

        if (false === $file) {
            throw new RuntimeException(
                'The file config/parameters.yml does not exist, please create it'
            );
        }

        $parameters = Yaml::parse($file);

        try {
            if (null === $parameters) {
                throw new RuntimeException(
                    'Your PIM is not configured. Please fill the file "app/config/parameters.yml"'
                );
            }

            return $this->getConnection($parameters)->getAttribute(PDO::ATTR_SERVER_VERSION);
        } catch (RuntimeException $e) {
            $parameters = Yaml::parse(file_get_contents(__DIR__.'/config/parameters_test.yml'));

            if (null === $parameters) {
                throw $e;
            }

            return $this->getConnection($parameters)->getAttribute(PDO::ATTR_SERVER_VERSION);
        }
    }

    /**
     * @param array $parameters
     *
     * @return PDO
     */
    protected function getConnection(array $parameters)
    {
        return new PDO(
            sprintf(
                'mysql:host=%s',
                $parameters['parameters']['database_host']
            ),
            $parameters['parameters']['database_user'],
            $parameters['parameters']['database_password']
        );
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

        preg_match('/([\-0-9]+)[\s]*([a-z]*)$/i', trim($val), $matches);

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

        return (float) $val;
    }

    /**
     * Get the list of mandatory requirements (all requirements excluding PhpIniRequirement)
     *
     * @return array
     */
    public function getMandatoryRequirements()
    {
        return array_filter($this->getRequirements(), function ($requirement) {
            return !($requirement instanceof PhpIniRequirement) && !($requirement instanceof PimRequirement);
        });
    }

    /**
     * Get the list of PHP ini requirements
     *
     * @return array
     */
    public function getPhpIniRequirements()
    {
        return array_filter($this->getRequirements(), function ($requirement) {
            return $requirement instanceof PhpIniRequirement;
        });
    }
}

/**
 * PimRequirement class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimRequirement extends Requirement
{
}
