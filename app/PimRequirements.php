<?php

use Symfony\Component\Yaml\Yaml;

require_once __DIR__.'/OroRequirements.php';

/**
 * Akeneo PIM requirements
 *
 * This class specifies all requirements and optional recommendations that are necessary
 * to install and run Akeneo PIM application
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 */
class PimRequirements extends OroRequirements
{
    const LOWEST_REQUIRED_MYSQL_VERSION = '5.7.0';
    const GREATEST_REQUIRED_MYSQL_VERSION = '5.8.0';

    /**
     * {@inheritdoc}
     */
    public function __construct(array $directoriesToCheck = [])
    {
        parent::__construct();

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

        $this->addPimRequirement(
            function_exists('apcu_store'),
            'Extension php5-apcu should be installed',
            'Install and enable <strong>php5-apcu</strong>'
        );

        $this->addPimRequirement(
            function_exists('bcmul'),
            'Extension bcmath should be installed',
            'Install and enable <strong>bcmath</strong> extension'
        );

        $this->addPimRequirement(
            $this->isConvertInstalled(),
            'The convert tool should be installed in order to transform images of the PAM',
            'Make sure to install <strong>Image Magick</strong> command line tool <strong>convert</strong>'
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
     * @param boolean     $fulFilled   Whether the requirement is fulfilled
     * @param string      $testMessage The message for testing the requirement
     * @param string      $helpHtml    The help text formatted in HTML for resolving the problem
     * @param string|null $helpText    The help text
     *     (when null, it will be inferred from $helpHtml, i.e. stripped from HTML tags)
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
     * {@inheritdoc}
     */
    public function getMandatoryRequirements()
    {
        return array_filter(parent::getMandatoryRequirements(), function ($requirement) {
            return !$requirement instanceof PimRequirement;
        });
    }

    /**
     * @return bool
     */
    protected function isConvertInstalled()
    {
        $imagickLauncher = new \Akeneo\Component\FileTransformer\Transformation\Image\ImageMagickLauncher();

        try {
            $imagickLauncher->getConvertBinaryPath();
        } catch (\RuntimeException $e) {
            return false;
        }

        return true;
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
                'mysql:host=%s;port=%s',
                $parameters['parameters']['database_host'],
                $parameters['parameters']['database_port']
            ),
            $parameters['parameters']['database_user'],
            $parameters['parameters']['database_password']
        );
    }
}

/**
 * PimRequirement class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 */
class PimRequirement extends Requirement
{
}
