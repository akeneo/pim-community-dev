<?php

require_once __DIR__ .'/OroRequirements.php';

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
class PimRequirements extends OroRequirements
{
    /**
     * @staticvar string
     */
    const REQUIRED_MYSQL_VERSION = '5.1';

    /**
     * {@inheritdoc}
     */
    public function __construct(array $directoriesToCheck = array())
    {
        parent::__construct();

        $this->addPimRequirement(
            !extension_loaded('php5-mysql'),
            'Extension php5-mysql should be installed',
            'Install and enable <strong>php5-mysql</strong>'
        );

        if (extension_loaded('php5-mysql')) {
            $this->addPimRequirement(
                version_compare(mysql_get_client_info(), self::REQUIRED_MYSQL_VERSION, '>='),
                sprintf('MySQL version must be at least %s', self::REQUIRED_MYSQL_VERSION),
                sprintf(
                    'Install MySQL %s or newer (installed version is %s)',
                    self::REQUIRED_MYSQL_VERSION,
                    mysql_get_client_info()
                )
            );
        }

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
     * {@inheritdoc}
     */
    public function getMandatoryRequirements()
    {
        return array_filter(parent::getMandatoryRequirements(), function ($requirement) {
            return !$requirement instanceof PimRequirement;
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
