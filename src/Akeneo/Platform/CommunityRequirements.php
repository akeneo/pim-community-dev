<?php

declare(strict_types=1);

namespace Akeneo\Platform;

use \PDO;
use Symfony\Component\Intl\Intl;
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
class CommunityRequirements
{
    const REQUIRED_PHP_VERSION = '7.2.0';
    const REQUIRED_GD_VERSION = '2.0';
    const REQUIRED_CURL_VERSION = '7.0';
    const REQUIRED_ICU_VERSION = '4.2';
    const LOWEST_REQUIRED_MYSQL_VERSION = '5.7.22';
    const GREATEST_REQUIRED_MYSQL_VERSION = '5.8.0';

    const REQUIRED_EXTENSIONS = [
        'apcu',
        'bcmath',
        'curl',
        'gd',
        'intl',
        'pdo_mysql',
        'soap',
        'xml',
        'zip',
        'exif',
        'imagick',
        'mbstring',
    ];

    public function __construct(string $baseDir, array $directoriesToCheck = [])
    {
        $this->baseDir = $baseDir;
        $this->directoriesToCheck = $directoriesToCheck;
    }

    /**
     * Generate the requirements by executing test and providing the result and the
     * associated messages
     */
    public function getRequirements(): array
    {
        $phpVersion  = phpversion();
        $gdVersion   = defined('GD_VERSION') ? GD_VERSION : null;
        $curlVersion = function_exists('curl_version') ? curl_version() : null;
        $icuVersion  = Intl::getIcuVersion();

        $requirements = [];

        $requirements[] = new Requirement(
            version_compare($phpVersion, self::REQUIRED_PHP_VERSION, '>='),
            sprintf('PHP version must be at least %s (%s installed)', self::REQUIRED_PHP_VERSION, $phpVersion),
            sprintf('You are running PHP version "<strong>%s</strong>", but needs at least PHP "<strong>%s</strong>" to run.
                Before using, upgrade your PHP installation, preferably to the latest version.',
                $phpVersion, self::REQUIRED_PHP_VERSION)
        );

        foreach (self::REQUIRED_EXTENSIONS as $requiredExtension) {
            $requirements[] = new Requirement(
                extension_loaded($requiredExtension),
                sprintf('%s extension should be available', $requiredExtension),
                sprintf('Install and enable the <strong>%s</strong> extension.', $requiredExtension)
            );
        }

        $requirements[] = new Requirement(
            null !== $gdVersion && version_compare($gdVersion, self::REQUIRED_GD_VERSION, '>='),
            'GD extension must be at least ' . self::REQUIRED_GD_VERSION,
            'Install and enable the <strong>GD</strong> extension at least ' . self::REQUIRED_GD_VERSION . ' version'
        );

        $requirements[] = new Requirement(
            null !== $icuVersion && version_compare($icuVersion, self::REQUIRED_ICU_VERSION, '>='),
            'icu library must be at least ' . self::REQUIRED_ICU_VERSION,
            'Install and enable the <strong>icu</strong> library at least ' . self::REQUIRED_ICU_VERSION . ' version'
        );

        $requirements[] = new Requirement(
            null !== $curlVersion && version_compare($curlVersion['version'], self::REQUIRED_CURL_VERSION, '>='),
            'cURL extension must be at least ' . self::REQUIRED_CURL_VERSION,
            'Install and enable the <strong>cURL</strong> extension at least ' . self::REQUIRED_CURL_VERSION . ' version',
            false
        );

        $mem = $this->getBytes(ini_get('memory_limit'));
        $requirements[] = new Requirement(
            $mem >= 512 * 1024 * 1024 || -1 == $mem,
            'memory_limit should be at least 512M',
            'Set the "<strong>memory_limit</strong>" setting in php.ini<a href="#phpini">*</a> to at least "512M".'
        );

        $directories = array(
            'web/bundles'
        );
        foreach ($directories as $directory) {
            $requirements[] = new Requirement(
                is_writable($this->baseDir.'/'.$directory),
                $directory.' directory must be writable',
                'Change the permissions of the "<strong>'.$directory.'</strong>" directory so that the web server can write into it.'
            );
        }

        $currentMySQLVersion = $this->getMySQLVersion();
        $requirements[] = new Requirement(
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

        $innodbPageSize = (int) $this->getMySQLVariableValue("innodb_page_size");
        $requirements[] = new Requirement(
            $innodbPageSize >= 8192,
            'Check support for correct innodb_page_size for utf8mb4 support',
            sprintf(
                'In order for the PIM to implement full UTF8 support via MySQL utf8mb4 charset,'.
                ' MySQL must have innodb_page_size >= 8KB.'.
                ' Current innodb_page_size is at "%s".'.
                ' Please change your MySQL server configuration to use the correct settings'.
                ' (innodb_page_size is at 16KB by default on MySQL 5.7)',
                $innodbPageSize
            )
        );

        $requirements[] = new Requirement(
            function_exists('exec'),
            'The exec() function should be enabled in order to run jobs',
            'Make sure the <strong>exec()</strong> function is not disabled in php.ini'
        );

        $requirements[] = new Requirement(
            ini_get('apc.enable_cli') == 1,
            'APCu should be enabled in CLI to get better performances',
            'Set <strong>apc.enable_cli</strong> to <strong>1</strong>',
            false
        );

        foreach ($this->directoriesToCheck as $directoryToCheck) {
            $requirements[] = new Requirement(
                is_writable($directoryToCheck),
                sprintf('%s directory must be writable', $directoryToCheck),
                sprintf('Change the permissions of the "<strong>%s</strong>" directory', $directoryToCheck)
            );
        }

        return $requirements;
    }

    /**
     * Returns a global MySQL configuration variable value
     */
    protected function getMySQLVariableValue(string $variableName) : ?string
    {
        $variableValue = null;

        $stmt = $this->getConnection()->query(
            sprintf("SELECT @@GLOBAL.%s", $variableName)
        );

        $variableValue = $stmt->fetchColumn();

        if (false === $variableValue) {
            $variableValue = null;
        }

        return $variableValue;
    }

    /**
     * Gets the MySQL server version thanks to a PDO connection.
     */
    protected function getMySQLVersion() : string
    {
        return $this->getConnection()->getAttribute(PDO::ATTR_SERVER_VERSION);
    }

    /**
     * If no connection is reached, or that "parameters.yml" do not exists, an
     * exception is thrown, then catch. If "parameters_test.yml" do not exists
     * either, then the exception is thrown again.
     * If it exits, an attempt to connect is done, and can result in an exception
     * if no connection is reached.
     */
    protected function getConnection() : PDO
    {
        $file = file_get_contents($this->baseDir.'/app/config/parameters.yml');

        if (false === $file) {
            throw new RuntimeException(
                'The file "app/config/parameters.yml" does not exist, please create it'
            );
        }

        $parameters = Yaml::parse($file);

        try {
            if (null === $parameters) {
                throw new RuntimeException(
                    'Your PIM is not configured. Please fill the file "app/config/parameters.yml"'
                );
            }
        } catch (RuntimeException $e) {
            $parameters = Yaml::parse(file_get_contents($this->baseDir.'/app/config/parameters_test.yml'));

            if (null === $parameters) {
                throw $e;
            }
        }

        return new PDO(
            sprintf(
                'mysql:port=%s;host=%s',
                $parameters['parameters']['database_port'],
                $parameters['parameters']['database_host']
            ),
            $parameters['parameters']['database_user'],
            $parameters['parameters']['database_password']
        );
    }

    protected function getBytes(string $val): float
    {
        if (empty($val)) {
            return (float) 0;
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
        }

        return (float) $val;
    }
}
